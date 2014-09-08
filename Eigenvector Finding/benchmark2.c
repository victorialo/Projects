/*
 *  THE CONTENTS OF THIS FILE WILL BE OVERWRITTEN WHILE GRADING.
 *  MODIFY AT YOUR OWN RISK.
 */
#include <signal.h>
#include <setjmp.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <float.h>
#include <math.h>
#include <time.h>
#include <sys/time.h>
#include <sys/resource.h>
#include "benchmark.h"

#define TOL (5e-4)

int segged = 0;
int in_eig = 0;
double average_flops = 0;
int num_tests = 1 << (sizeof(int) - 2);

jmp_buf env;
int val;

void safe_eig(float *v, float *A, float *u, size_t n, unsigned iters) {
    in_eig = 1;
    val = sigsetjmp(env, SIGSEGV);
    if (!val) {
        eig (v, A, u, n, iters);
    }
    in_eig = 0;
    return;
}


void segfault_sigaction(int signal) //, siginfo_t *si, void *arg)
{
    segged = 1;
    if (!in_eig) {
        exit(0);
    } else {
        siglongjmp(env, 1);
    }
}

// NOTE : this constant varies per machine
// this is for the hive machines (Intel Xeon E5620 @ 2.4GHz)
#define TSC_PER_SECOND (2400*1e6)
static __inline__ unsigned long long rdtsc(void)
{
    unsigned hi, lo;
    __asm__ __volatile__ ("rdtsc" : "=a"(lo), "=d"(hi));
    return ( (unsigned long long)lo)|( ((unsigned long long)hi)<<32 );
}

void die (const char* message)
{
    perror (message);
    exit (EXIT_FAILURE);
}

void fill (float* p, int n)
{
    for (int i = 0; i < n; ++i)
        p[i] = 2 * drand48() - 1; // Uniformly distributed over [-1, 1]
}

void absolute_value (float *p, int n)
{
    for (int i = 0; i < n; ++i)
        p[i] = fabsf (p[i]);
}

void symmetrify (float *A, int n) {
    float *tmp = calloc(n *n, sizeof(float));
    if (!tmp) {
        fprintf(stderr, "Out of memory\n");
        exit(-1);
    }
    for (int i = 0; i < n; i += 1)
        for (int j = 0; j < n; j += 1)
            for (int k = 0; k < n; k += 1)
                tmp[j + i*n] += A[k + i*n] * A[k + j*n];
    memcpy(A, tmp, n * n * sizeof(float));
    free(tmp);
}

/* the benchmarking program */
int main( int argc, char **argv )
{
    /* Set things up so we just set segged to true whenever we get a segfault. */
    signal(SIGSEGV, segfault_sigaction);
    srand(time(NULL));

    /* Multiples-of-32, +/- 1 */

    int test_sizes[] =  {127,128,129,159,160,161,191,192,193,223,224,225,255,256,257,287,288,289,319,320,321,351,352,353,383,384,385,415,416,417,447,448,449,479,480,481,511,512,513,543,544,545,575,576,577,607,608,609,639,640,641,671,672,673,703,704,705,735,736,737,767,768,769,799,800,801,831,832,833,863,864,865,895,896,897,927,928,929,959,960,961,991,992,993,1023,1024};

    const unsigned ITERATIONS = 4; //Almost certainly not going to give good convergence, but should be enough for benchmarking. We recommend increasing ITERATIONS only at the very end of your testing, as it makes benching much slower.x

    int num_tests = sizeof(test_sizes) / sizeof(test_sizes[0]);

    /* screw up memory leakers */
    /* REMOVED because a 0 for leaking memory is a bit harsh. */
    /* struct rlimit lim; */
    /* getrlimit(RLIMIT_AS, &lim); */
    /* lim.rlim_cur = 1024*1024*512; */
    /* lim.rlim_max = 1024*1024*512; */
    /* setrlimit(RLIMIT_AS, &lim); */
    /* for (int i = 0; i < 50; i += 1) { */
    /*     int n = 1023; */
    /*     float *A = malloc(n*n*sizeof(float)); */
    /*     float *u = malloc(n*n*sizeof(float)); */
    /*     float *v = malloc(n*n*sizeof(float)); */
    /*     safe_eig (v, A, u, n, 1); */
    /*     free(A); */
    /*     free(u); */
    /*     free(v); */
    /* } */


    /* For each test size */
    for (int isize = 0; isize < num_tests; isize++) {
        int n = test_sizes[isize];

        /* Create and fill 3 random matrices A,B,C*/
        float *A = malloc(n*n*sizeof(float));
        float *u = malloc(n*n*sizeof(float));
        float *v = malloc(n*n*sizeof(float));

        fill (A, n*n);
        if (n >= 500)
            symmetrify (A, n);
        fill (u, n*n);
        fill (v, n*n);

        float *A2 = malloc(n*n*sizeof(float));
        float *u2 = malloc(n*n*sizeof(float));
        float *v2 = malloc(n*n*sizeof(float));

        memcpy(A2, A, n * n * sizeof(float));
        memcpy(u2, u, n * n * sizeof(float));
        memcpy(v2, v, n * n * sizeof(float));
        double Gflop_s, seconds = -1.0;

        segged = 0;
        for (int n_iterations = 1; seconds < 0.1; n_iterations *= 2) {
            /* warm-up + alignment check */
            safe_eig (v + 1, A + 1, u + 1, n - 1, ITERATIONS);
            safe_eig (v, A, u, n, ITERATIONS);


            /* measure time */
            unsigned long long start, end;
            memcpy(u, u2, n * sizeof(float));
            start = rdtsc();
            for( int i = 0; i < n_iterations; i++ )
                safe_eig (v, A, u, n, ITERATIONS);
            end = rdtsc();
            seconds = (end - start)/TSC_PER_SECOND;

            /* compute Gflop/s rate */
            Gflop_s = 1e-9 * n_iterations * ITERATIONS * n * (3*n + 2*n*n) / seconds;
        }

        //printf( "%d by %d matrix \t %g Gflop/s\n", n, n, Gflop_s );

        /* Ensure that error does not exceed the theoretical error bound */

        /* Set initial V to 0 and do power iteration */
        memset(v, 0, sizeof(float) * n * n);
        memcpy(u, u2, n * n * sizeof(float));
        safe_eig (v, A, u, n, ITERATIONS);

        /* Calculate naive solution */
        if (segged) {
            free(A);
            free(u);
            free(v);
            free(A2);
            free(u2);
            free(v2);
            continue;
        }
        memset(v2, 0, sizeof(float) * n * n);
        eig_naive(v2, A2, u2, n, ITERATIONS);

        /* Subtract the maximum allowed roundoff from each element of C */
        float diff = 0;
        for (int i = 0; i < n * n; i += 1) {
            diff += fabsf(v[i] - v2[i]);
        }

        /* Compare error to magnitude of solution. */
        float mag = 0;
        for (size_t i = 0; i < n * n; i += 1) {
            mag += v2[i] * v2[i];
        }
        mag = sqrt(mag);

        if (!(diff / mag <= TOL)) {
            /* Failed test case. 0 Gflop/s. */
        } else {
            average_flops += Gflop_s;
        }
        printf("%g\n", average_flops / num_tests);
        printf("%g\n", average_flops / num_tests);
        printf("%g\n", average_flops / num_tests);
        printf("%g\n", average_flops / num_tests);
        printf("%g\n", average_flops / num_tests);


        free(A);
        free(u);
        free(v);
        free(A2);
        free(u2);
        free(v2);
    }
    printf("%g\n", average_flops / num_tests);
    printf("%g\n", average_flops / num_tests);
    printf("%g\n", average_flops / num_tests);
    printf("%g\n", average_flops / num_tests);
    printf("%g\n", average_flops / num_tests);

    return 0;
}

void eig_naive(float *v, float *A, float *u, size_t n, unsigned iters) {
    for (size_t k = 0; k < iters; k += 1) {
        /* v_k = Au_{k-1} */
        memset(v, 0, n * n * sizeof(float));
        for (size_t l = 0; l < n; l += 1) {
            for (size_t j = 0; j < n; j += 1) {
                for (size_t i = 0; i < n; i += 1) {
                    v[i + l*n] += u[j + l*n] * A[i + n*j];
                }
            }
        }
        /* mu_k = ||v_k|| */
        float mu[n];
        memset(mu, 0, n * sizeof(float));
        for (size_t l = 0; l < n; l += 1) {
            for (size_t i = 0; i < n; i += 1) {
                mu[l] += v[i + l*n] * v[i + l*n];
            }
            mu[l] = sqrt(mu[l]);
        }

        /* u_k = v_k / mu_k */
        for (size_t l = 0; l < n; l += 1) {
            for (size_t i = 0; i < n; i += 1) {
                u[i + l*n] = v[i + l*n] / mu[l];
            }
        }
    }
}