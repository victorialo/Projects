#include <string.h>
#include <math.h>
#include "benchmark.h"
#include <nmmintrin.h>
#include "xmmintrin.h"

/* Returns the indexx corresponding to an element at row R and
 * column C in a square array of width W. */
int indexx(int r, int c, int w) {
    return r * w + c;
}

void swap(float *x, float *y) {
    /* Optional function */
    float temp = *x;
    *x = *y;
    *y = temp;

}

void transpose(float *arr, size_t width) {
    int row, col;
    #pragma omp parallel for collapse(2)
    for (row = 0; row < width - 1; row ++) {
        for (col = 0; col < width - 1; col ++) {
            swap(&arr[indexx(row, col, width)], &arr[indexx(col, row, width)]);
        }
    }
}



void eig(float *v, float *A, float *u, size_t n, unsigned iters) {
    /* TODO: write a faster version of eig */
    // eig_naive(v, A, u, n, iters);
    size_t k;
    size_t l;
    size_t j;
    size_t i;
    size_t tail;

    // float* tran = malloc(n*n*sizeof(float));
   // /* tran =*/ transpose(A, n);

    for (k = 0; k < iters; k += 1) {
        /* v_k = Au_{k-1} */
        memset(v, 0, n * n * sizeof(float));
        /*for (size_t l = 0; l < n; l += 1) {
            for (size_t i = 0; i < n; i += 1) {
                for (size_t j = 0; j < n; j += 1) {
                    v[i + l*n] += u[j + l*n] * A[i + n*j];
                }
            }
        }*/

        
                    /*for (i = 0; i < n/4*4; i += 4) {
                        // int a = (int)_mm512_fmadd_epi32(l,n,i);
                        v2 = _mm_loadu_ps(&(v[i+l*n]));
                        A2 = _mm_loadu_ps(&(A[i+n*j]));
                        v[i + l*n] += u2 * A[i + n*j];
                        v[i + l*n + 1] +=  u2 * A[i + n*j + 1];
                        v[i + l*n + 2] += u2 * A[i + n*j + 2];
                        v[i + l*n + 3] += u2 * A[i + n*j + 3];
                        v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                        _mm_storeu_ps(&(v[i+l*n]), v2);
                    }*/

        // #pragma omp parallel
        // {
            __m128 v2 = _mm_setzero_ps();
            __m128 u3 = _mm_setzero_ps();
            __m128 A2 = _mm_setzero_ps();
            float u2;
            #pragma omp parallel for collapse(2) firstprivate(v2,u3,A2,u2,i,tail)  
            for (l = 0; l < n; l += 1) {
                for (j = 0; j < n; j += 1) {
                    u2 = u[j + l*n];
                    u3 = _mm_set1_ps(u2);
                	for (i = 0; i < n/16*16; i += 16) {
                        /*size_t var1 = i+l*n;
                        size_t var2 = i+n*j;*/
                        float* var1 = v+i+l*n;
                        float* var2 = A+i+n*j;
                        // int a = (int)_mm512_fmadd_epi32(l,n,i);
                        // v[i + l*n] += u2 * A[i + n*j];
                        v2 = _mm_loadu_ps(var1);
                        A2 = _mm_loadu_ps(var2);
                        v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                        _mm_storeu_ps(var1, v2);

                        v2 = _mm_loadu_ps(var1+4);
                        A2 = _mm_loadu_ps(var2+4);
                        v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                        _mm_storeu_ps(var1+4, v2);

                        v2 = _mm_loadu_ps(var1+8);
                        A2 = _mm_loadu_ps(var2+8);
                        v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                        _mm_storeu_ps(var1+8, v2);

                        v2 = _mm_loadu_ps(var1+12);
                        A2 = _mm_loadu_ps(var2+12);
                        v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                        _mm_storeu_ps(var1+12, v2);
                    }
                    if (i != n) {
                        size_t var3;
                        /*for (tail2 = n/16*16; tail2 < n/8*8; tail2+=8) {
                            var3 = l*n + tail2;
                            v2 = _mm_loadu_ps(&(v[var3]));
                            A2 = _mm_loadu_ps(&(A[tail + j*n]));
                            v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                            _mm_storeu_ps(&(v[var3]), v2);

                            v2 = _mm_loadu_ps(&(v[var3+4]));
                            A2 = _mm_loadu_ps(&(A[tail + j*n+4]));
                            v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                            _mm_storeu_ps(&(v[var3+4]), v2);
                        }*/

                        for (tail = n/16*16; tail < n/4*4; tail+=4) {
                            var3 = l*n + tail;
                            v2 = _mm_loadu_ps(&(v[var3]));
                            A2 = _mm_loadu_ps(&(A[tail + j*n]));
                            v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                            _mm_storeu_ps(&(v[var3]), v2);

                            /*v2 = _mm_loadu_ps(&(v[var3+4]));
                            A2 = _mm_loadu_ps(&(A[tail + j*n+4]));
                            v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                            _mm_storeu_ps(&(v[var3+4]), v2);*/
                        }
                        if(tail != n) {
                            for (tail = n/4*4; tail < n; tail++) {
                                v[l*n + tail] += u2 * A[tail + j*n];
                                // size_t var3 = l*n + tail;
                                // v2 = _mm_loadu_ps(&(v[var3]));
                                // A2 = _mm_loadu_ps(&(A[tail + j*n]));
                                // v2 = _mm_add_ps(v2, _mm_mul_ps(u3, A2));
                                // _mm_storeu_ps(&(v[var3]), v2);
                            }
                        }
                    }
                }
                /*for (int tailj = 0; tailj < (j-n); tailj++) {
                    v[tailj] += u[tailj + l*n] * A[tailj + j*n];
                } */
            }
            /*for (int taill = 0; taill < (l-n); taill++) {
                v[taill] += u[taill + n*n] * A[taill + j*n];
            }*/
        // }
        


        /* mu_k = ||v_k|| */
        float mu[n] /*= _mm_setzero_ps()*/; 
        // float temp;
        // __m128 i2 = _mm_loadu_ps(&i);
        // __m128 l2 = _mm_loadu_ps(&l);
        // __m128 temp = _mm_setzero_ps();
        
        // __m128 mu2 = _mm_setzero_ps();
        // __m128 v2 = _mm_setzero_ps();

        memset(mu, 0, n * sizeof(float));
        // int blocksize = 1;
        // int m,k;
        #pragma omp parallel for /*collapse(2)*/
        for (l = 0; l < n; l += 1) {
            // temp = _mm_add_ps(i2, _mm_mul_ps((__m128)l, (__m128)n)); 
            // #pragma omp parallel for
            for (i = 0; i < n/4*4; i += 4) {
                // for (k = l; k < l+blocksize && k < n; k++) {
                    // for (m = i; m < i+blocksize && m < n; m++) {
                /*mu2 = _mm_loadu_ps(&(mu[i+l*n]));
                v2 = _mm_loadu_ps(&(v[i+l*n]));*/
                // temp = i+l*n;
                mu[l] += v[i + l*n] * v[i + l*n];
                mu[l] += v[i + l*n + 1] * v[i + l*n + 1];
                mu[l] += v[i + l*n + 2] * v[i + l*n + 2];
                mu[l] += v[i + l*n + 3] * v[i + l*n + 3];

                        // mu[k] += v[m + k*n] * v[m + k*n];
                /*mu2 = _mm_add_ps(mu2, _mm_mul_ps(v2, v2));
                _mm_storeu_ps(&(mu[i+l*n]), mu2);*/
                    // }
                // }
            }
            if (i != n) {
                // #pragma omp parallel for 
                for (tail = n/4*4; tail < n; tail++) {
                    mu[l] += v[tail + l*n] * v[tail + l*n];
                }
            }
            // mu2 = _mm_loadu_ps(&(mu[l]));
            mu[l] = sqrt(mu[l]);
            /*mu2 = _mm_rsqrt_ps(mu2);
            _mm_storeu_ps(&(mu[l]),mu2);*/
            

        }
       

        /* u_k = v_k / mu_k */
        #pragma omp parallel for collapse(1)
        for (l = 0; l < n; l += 1) {
            float m = mu[l];
            // #pragma omp parallel for
            for (i = 0; i < n/4*4; i += 4) {
                size_t var4 = i + l*n;
                // temp = _mm_add_ps((__m128)i, _mm_mul_ps((__m128)l, (__m128)n)); 
                u[var4] = v[var4] / m; 
                u[var4 + 1] = v[var4 + 1] / m;
                u[var4 + 2] = v[var4 + 2] / m;
                u[var4 + 3] = v[var4 + 3] / m;
            }
            if (i != n) {
                for (tail = n/4*4; tail < n; tail++) {
                    u[tail + l*n] = v[tail + l*n] / m;
                }
            }
        }
    }
    // free(tran);
}

