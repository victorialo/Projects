/*
 * CS61C Spring 2014 Project2
 * Reminders:
 *
 * DO NOT SHARE CODE IN ANY WAY SHAPE OR FORM, NEITHER IN PUBLIC REPOS OR FOR DEBUGGING.
 *
 * This is one of the two files that you should be modifying and submitting for this project.
 */
import java.io.DataInput;
import java.io.DataOutput;
import java.io.IOException;
import java.util.*;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.lang.Math;

import org.apache.hadoop.fs.FileSystem;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.io.*;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.input.SequenceFileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.mapreduce.lib.output.TextOutputFormat;
import org.apache.hadoop.mapreduce.lib.output.SequenceFileOutputFormat;
import org.apache.hadoop.util.GenericOptionsParser;

public class SolveMoves {
    public static class Map extends Mapper<IntWritable, MovesWritable, IntWritable, ByteWritable> {
        /**
         * Configuration and setup that occurs before map gets called for the first time.
         *
         **/
        @Override
        public void setup(Context context) {
        }

        /**
         * The map function for the second mapreduce that you should be filling out.
         */
        @Override
        public void map(IntWritable key, MovesWritable val, Context context) throws IOException, InterruptedException {
            /* YOUR CODE HERE */
            for (int i : val.getMoves()) {
                context.write(new IntWritable(i), new ByteWritable(val.getValue()));
                /*(parent, child)*/
            }
        }
    }

    public static class Reduce extends Reducer<IntWritable, ByteWritable, IntWritable, MovesWritable> {

        int boardWidth;
        int boardHeight;
        int connectWin;
        boolean OTurn;
        /**
         * Configuration and setup that occurs before map gets called for the first time.
         *
         **/
        @Override
        public void setup(Context context) {
            // load up the config vars specified in Proj2.java#main()
            boardWidth = context.getConfiguration().getInt("boardWidth", 0);
            boardHeight = context.getConfiguration().getInt("boardHeight", 0);
            connectWin = context.getConfiguration().getInt("connectWin", 0);
            OTurn = context.getConfiguration().getBoolean("OTurn", true);
        }

        /**
         * The reduce function for the first mapreduce that you should be filling out.
         */
        @Override
        public void reduce(IntWritable key, Iterable<ByteWritable> values, Context context) throws IOException, InterruptedException {
            /* YOUR CODE HERE */
            /*(parent, child) > minimax then filter parents > (child, parent)*/
            boolean flag = false; //variable to represent if any of boards are also in possible moves
            int beststatus = 0; //variable to store if win, lose, draw
            byte bestmovestoend = 0; //best move according to status
            ArrayList<Byte> winvalues = new ArrayList<Byte>();
            ArrayList<Byte> losevalues = new ArrayList<Byte>();
            ArrayList<Byte> drawvalues = new ArrayList<Byte>();

            int win = 1; //pretend it's O's turn
            int lose = 2;
            int draw = 3;

            if (!OTurn) { //but if it's not O's turn
                win = 2;
                lose = 1;
            }

            for (ByteWritable b : values) {
                byte i = b.get();
                int firstsix = i >> 2;
                int lasttwo = i & 3;
                if (firstsix == 0) { //if child is in possiblemoves, valid board
                    flag = true;
                }
                if (lasttwo == win) {
                    winvalues.add(b.get()); //.add(i);
                }
                if (lasttwo == draw) {
                    drawvalues.add(b.get());
                }
                if (lasttwo == lose) {
                    losevalues.add(b.get());
                }
            }

            if (flag == false) { //if board not in possibleboards, throw it out
                return;
            }

            if (!winvalues.isEmpty()) {
                beststatus = win;
                bestmovestoend = winvalues.get(0);
                for (int i = 0; i < winvalues.size(); i++) {
                    byte current = (byte) (bestmovestoend >> 2);
                    byte compare = (byte) (winvalues.get(i) >> 2);
                    if (compare < current) {
                        bestmovestoend = winvalues.get(i);
                    }
                }
            } else if (!drawvalues.isEmpty()) {
                beststatus = draw;
                bestmovestoend = drawvalues.get(0);
                for (int i = 0; i < drawvalues.size(); i++) {
                    byte current = (byte) (bestmovestoend >> 2);
                    byte compare = (byte) (drawvalues.get(i) >> 2);
                    if (compare > current) {
                        bestmovestoend = drawvalues.get(i);
                    }
                }
            } else if (!losevalues.isEmpty()) {
                beststatus = lose;
                bestmovestoend = losevalues.get(0);
                for (int i = 0; i < losevalues.size(); i++) {
                    byte current = (byte) (bestmovestoend >> 2);
                    byte compare = (byte) (losevalues.get(i) >> 2);
                    if (compare > current) {
                        bestmovestoend = losevalues.get(i);
                    }
                }
            }

            /* take key, turn it into game state with unhasher. PARENT GENERATOR **/
            String board = Proj2Util.gameUnhasher(key.get(), boardWidth, boardHeight); //unhash the key into string (the board)
            ArrayList<Integer> parentarray = new ArrayList<Integer>();

            for (int loop = 0; loop < board.length(); loop += boardHeight) { // for loop that hops to each column
                StringBuilder newboard = new StringBuilder(board);
                for (int subloop = loop; subloop < loop + boardHeight; subloop++) { // for loop that puts an 'X' or 'O' in the first available spot in each column
                    if (newboard.charAt(subloop) == ' ') { // if no space in the whole column, will just return
                        if (subloop == loop) { //if the lowest space is white space, skip to next column
                            break;
                        } else { //if the white space we found is not the lowest...
                            char symbol = newboard.charAt(subloop - 1);
                            if (symbol == 'X' && OTurn) { //if the previous player was X, remove this X
                                newboard.setCharAt(subloop - 1, ' '); //turn it into space
                                int parentboard = Proj2Util.gameHasher(newboard.toString(), boardWidth, boardHeight); //generate new parent board
                                parentarray.add(parentboard);
                                break;
                            } else if (symbol == 'O' && !OTurn) { //if the previous player was O, remove this O
                                newboard.setCharAt(subloop - 1, ' ');
                                int parentboard = Proj2Util.gameHasher(newboard.toString(), boardWidth, boardHeight);
                                parentarray.add(parentboard);
                                break;
                            } else {
                                break;
                            }
                        }
                    }
                }
                if (newboard.charAt(loop + boardHeight - 1) == 'X' && OTurn) {
                    newboard.setCharAt(loop + boardHeight - 1, ' '); //turn it into space
                    int parentboard = Proj2Util.gameHasher(newboard.toString(), boardWidth, boardHeight); //generate new parent board
                    parentarray.add(parentboard);
                } else if (newboard.charAt(loop + boardHeight - 1) == 'O' && !OTurn) {
                    newboard.setCharAt(loop + boardHeight - 1, ' '); //turn it into space
                    int parentboard = Proj2Util.gameHasher(newboard.toString(), boardWidth, boardHeight); //generate new parent board
                    parentarray.add(parentboard);
                }
            }

            //if (OTurn) {
            //System.out.println("START: current player is O");
            //} else {
            //System.out.println("START: current player is X");
            //}
            int size = parentarray.size(); //now array copied, saves size
            int[] intarray = new int[size]; //initializes int array
            for (int i = 0; i < size; i++) { //copies from arraylist into int array
                intarray[i] = parentarray.get(i);
                //System.out.println("'" + Proj2Util.gameUnhasher(intarray[i], boardWidth, boardHeight) + "'");
            }

            //System.out.println("best status is: " + beststatus);
            //System.out.println("best movestoend is: " + bestmovestoend);
            //System.out.println("this is the board '" + Proj2Util.gameUnhasher(key.get(), boardWidth, boardHeight) + "'");
            MovesWritable tobereturned = new MovesWritable((byte) beststatus, (bestmovestoend >> 2) + 1, intarray); //creates the move writable object of current key
            context.write(key, tobereturned); //returns the same key and it's associated moveswritable object
        }
    }
}