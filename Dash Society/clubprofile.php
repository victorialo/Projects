<?php
 session_start();
 
include('../session.php');
include('../query.php');
$userrow = checksession();
if($userrow == false)
{

die();

}
$playerid = $_SESSION['userid'];
if(isset($_GET["clubid"]))
 {
 
 $clubid = $_GET["clubid"];
 
 }
 else
 {
 $clubid = 0;
 }
function horseimage($breed,$color)
{

              $image = $color;
              
              if($breed == "Quarter Horse")
              {
              $image .= "QuarterHorseleft.png";        
              }
              else if($breed == "Arabian")
              {
              $image .= "Arabianleft.png";                          
              }
              else if($breed == "Thoroughbred")
              {
              $image .= "Thoroughbredleft.png"; 
              }
              return $image;
}              

?>

<html>
  <head>
  <link rel="stylesheet" href="../styles/profile.css" type="text/css" />
  </head>
  <title>Club Profile</title>
  <body>
  
  <div id="content">
  <h1> 
  <?php 

         if($clubid == 0)
         {
        $clubmember = Query("Select clubid from clubmembership where playerid = ".$playerid." ");
          if($clubmember["clubid"] == 0)
        {
        echo "You are not a part of any clubs. Talk to Club Offical if you want to create a club";
    
         return;   
        }
          $clubid = $clubmember["clubid"];
         }        
        $clubrow = Query("Select clubid,name,description from club where clubid = ".$clubid." ");
        

            
echo $clubrow["name"];
  ?></h1>
  <center>
    
    
    
  <BR><BR>
  </center>
  <div id="info">

  <h2>Info</h2>
  
<BR><center>
<big>Members:</big>
<hr />

<?php
        $member = mysql_query("select player.playerid as playerid,name from player,clubmembership where clubid = ".$clubrow["clubid"]." and player.playerid = clubmembership.playerid ");
        
        while ($memberrow = mysql_fetch_array($member))
        {
             echo "<div><a href='profile.php?playerid=".$memberrow["playerid"]."'>".$memberrow["name"]."</a></div>";    
                  
        }     

  


 // $clubrow = Query("Select name,description from club "); 
 // echo $clubrow["name"];
 // echo "<br />";
 // echo $clubrow["description"]; 

?> 

  
  </div>
  <div class="padding"></div>
  
  <div id="horses">
  <h2>Description</h2>
  <br />
  <?php echo $clubrow["description"]; ?>
  <br />
  </div>
  <div class="padding"></div>
  
  <div id="horses">
  <h2>Horses</h2>

  <center>
<?php  
 $horsequery = mysql_query("Select name,breed,sex,color from horse,horsestats where clubid = ".$clubrow["clubid"]." and horse.horseid = horsestats.horseid ");
    $horsenum = mysql_num_rows($horsequery);
    
   
    echo "<br />Club has ".$horsenum." horse(s) in its ownership<br />";
?>
    <BR><table>  
<th><big>Image</big></th>
<th><big>Name</big></th>
<th><big>Breed</big></th>
<th><big>Gender</big></th>
<th><big>Color</big></th>
</tr>  
  <?php
  $horsesquery = mysql_query("SELECT horse.horseid as horseid,name,breed,sex,color FROM horse,horsestats where clubid = ".$clubrow["clubid"]." and horse.horseid = horsestats.horseid ");
  while ($horsesrow = mysql_fetch_array($horsesquery))
  {
echo "<tr>";
echo "<td><a href='horseprofile.php?horseid=".$horsesrow["horseid"]."'><img src=../images/".horseimage($horsesrow["breed"],$horsesrow["color"])." width=100px></a>";
echo "<td><a href='horseprofile.php?horseid=".$horsesrow["horseid"]."'>".$horsesrow["name"]."</a></td>";
echo "<td>".$horsesrow["breed"]."</td>";
echo "<td>".$horsesrow["sex"]."</td>";
echo "<td>".$horsesrow["color"]."</td>";
echo "</tr>";
  }
  
  
  
  ?>
  

</table>
</center>
  <?php 
  //  while ($horserow = mysql_fetch_array($horsequery))
  //  {
   //     echo "".$horserow["name"]." - ".$horserow["color"]." ".$horserow["breed"]." ".$horserow["sex"]."<br />";
  //      
  //  }
  ?>
 
  
  <br><br><br><br><br>
  </div>
  
  
  <div class="padding"></div>
  
  <div class="box"">
  <h2>Options</h2>
<center><BR>  
<?php echo "<div onclick='Sendmessage(\"leaveclub\");Player.chatid=0;Player.viewchat=\"world\"' ><b>Leave Club?</b></div>"; ?>  

</center>
</table>
</center>
         <?php //include('clubmanage.php'); ?>
  </div>
 
  </div>  </div>
  </body>
  </html>
