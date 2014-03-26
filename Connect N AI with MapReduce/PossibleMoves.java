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


public class PossibleMoves {
    public static class Map extends Mapper<IntWritable, MovesWritable, IntWritable, IntWritable> {
        int boardWidth;
        int boardHeight;
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
            OTurn = context.getConfiguration().getBoolean("OTurn", true);
        }

        /**
         * The map function for the first mapreduce that you should be filling out.
         * This job is called once per level in the full game tree, with each call to map getting as input the output of the previous map phase.
         */
        @Override
        public void map(IntWritable key, MovesWritable val, Context context) throws IOException, InterruptedException {
            /** YOU CODE HERE
            //take key, turn it into game state with unhasher. **/
            String board = Proj2Util.gameUnhasher(key.get(), boardWidth, boardHeight); //unhash the key into string (the board)
            if (val.getStatus() != 0) {
                return;
            }
            for (int loop = 0; loop < board.length(); loop += boardHeight) { // for loop that hops to each column
                StringBuilder newboard = new StringBuilder(board);
                for (int subloop = loop; subloop < loop + boardHeight; subloop++) { // for loop that puts an 'X' or 'O' in the first available spot in each column
                    if (newboard.charAt(subloop) == ' ') { // if no available spot will just return
                        if (OTurn == true) {
                            newboard.setCharAt(subloop, 'O'); //if available spot, put curent player in and emit this child
                            IntWritable hedwig = new IntWritable(Proj2Util.gameHasher(newboard.toString(), boardWidth, boardHeight));
                            context.write(hedwig, key);
                            break;
                        } else {
                            newboard.setCharAt(subloop, 'X');
                            IntWritable hedwig = new IntWritable(Proj2Util.gameHasher(newboard.toString(), boardWidth, boardHeight));
                            context.write(hedwig, key);
                            break;
                        }
                    }
                }
            }
        }
    }

    public static class Reduce extends Reducer<IntWritable, IntWritable, IntWritable, MovesWritable> {

        int boardWidth;
        int boardHeight;
        int connectWin;
        boolean OTurn;
        boolean lastRound;
        /**
         * Configuration and setup that occurs before reduce gets called for the first time.
         *
         **/
        @Override
        public void setup(Context context) {
            // load up the config vars specified in Proj2.java#main()
            boardWidth = context.getConfiguration().getInt("boardWidth", 0);
            boardHeight = context.getConfiguration().getInt("boardHeight", 0);
            connectWin = context.getConfiguration().getInt("connectWin", 0);
            OTurn = context.getConfiguration().getBoolean("OTurn", true);
            lastRound = context.getConfiguration().getBoolean("lastRound", true);
        }

        /**
         * The reduce function for the first mapreduce that you should be filling out.
         */
        @Override
        public void reduce(IntWritable key, Iterable<IntWritable> values, Context context) throws IOException, InterruptedException {
            /** YOU CODE HERE
             The MovesWritable object will at this point encapsulate all the possible parents and a win/loss/tie value for this state.
              The parents is simply a combined list of all corresponding values for the provided key.
               The win/loss/tie value needs to be determined by you by scanning through the game state representation.

               If there are connectWin pieces in a row somewhere in the game board,
                you should determine this and set the correct win value (0b01 for a O win, and 0b10 for a X win).
                If this is the lastRound and you don't find a win, then you should set the status to a tie (0b11).
             If none of these conditions exist, then the value for this state is undecided (0b00).**/
            String board = Proj2Util.gameUnhasher(key.get(), boardWidth, boardHeight); //unhash to board state
            int state = 0;
            int movestoend = 0;
            if (Proj2Util.gameFinished(board, boardWidth, boardHeight, connectWin)) { //check if there is a winner
                if (OTurn) {
                    state = 1;
                } else {
                    state = 2;
                }
            } else if (lastRound) {
                state = 3;
            }
            ArrayList<Integer> arr = new ArrayList<Integer>(); //makes arraylist of intwritables
            Iterator<IntWritable> iter = values.iterator(); // make iterator of iterable
            while (iter.hasNext()) { //while invoking iterator, copies into array
                arr.add(iter.next().get());
            }
            int size = arr.size(); //now array copied, saves size
            int[] intarray = new int[size]; //initializes int array
            for (int loop = 0; loop < size; loop++) { //copies from arraylist into int array
                intarray[loop] = arr.get(loop);
            }
            MovesWritable tobereturned = new MovesWritable((byte) state, movestoend, intarray); //creates the move writable object of current key
            context.write(key, tobereturned); //returns the same key and it's associated moveswritable object
        }
    }
}
