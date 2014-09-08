Projects
========
A little portfolio of some coding I've done. 


Ants vs. Some Bees
----------------
[Documentation/Description](http://www-inst.eecs.berkeley.edu/~cs61a/sp13/projects/ants/ants.html)

A spinoff of "Plants vs. Zombies" written in Python (mostly ants.py). Partner project with Shuochen Huang for CS 61A. 

####To run:####
*Prerequisites:* Have some version of Python3 installed.

	python3 ants_gui.py
	
For text-based version: 
	python3 ants.py


A Simple Enigma
-----------------
[Documentation/Description](https://inst.eecs.berkeley.edu/~cs61b/fa13/labs/proj0.pdf)

A replica of the Enigma encryption/decryption machine used in World War II written in Java. Individual project for Hilfinger's CS 61B. 


####To run:####
*Prerequisites:* Have some version of the Java SDK installed.

	java enigma.Main < INPUT-FILE > OUTPUT-FILE
(input and output files optional)


Connect N AI with MapReduce
-----------------
[Documentation/Description](http://www-inst.eecs.berkeley.edu/~cs61c/sp14/projs/02/)

An AI to solve for the most effective moves to win a game of Connect N using minimax game trees, written in Java and using MapReduce with the Hadoop framework. Partner project with Maryam Labib for Garcia's CS 61C. 

####To run:####
*Prerequisites:* Have some version of the Java SDK, Python, and the Hadoop framework installed. Hadoop files are included in the lib directory.

	make
	
	make proj2 WIDTH=n HEIGHT=m CONNECT=i
including the all-caps, where WIDTH is the width of the board, HEIGHT is the height of the board, and CONNECT is the number of pieces for a connect/win.
	
	python TUI.py w h c
where w is the board width, h is the board height, and c is the number of pieces for a connect/win. 

-----------
Taken down temporarily - please email me if you'd like to see this!


Eigenvector Finding
-----------------
[Documentation/Description](http://www-inst.eecs.berkeley.edu/~cs61c/su14/proj/02/proj2.pdf)

Optimized code to find eigenvectors to matrices from ~1 GFlop/sec to ~30+ GFlop/sec. Individual project for CS 61C. 

####To run:####
*Prerequisites:* Have some version of C installed. 
	
	make
	./bench-fast

