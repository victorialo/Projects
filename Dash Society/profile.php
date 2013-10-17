<?php
 session_start();

include('../session.php');
$userrow = checksession();
if($userrow == false)
{

die("You are not logged in");

}
include('../query.php');

if(isset($_GET["playerid"]))
 {
        if($_GET["playerid"] != 0)
        $playerid = $_GET["playerid"];
        else
        $playerid = $_SESSION['userid'];     
 
 }
 else
 {
 $playerid = $_SESSION['userid'];
 }



 
 
 $playerquery = mysql_query("SELECT name,loggedin,la,lo,location,jobid,class FROM player where playerid = ".$playerid." limit 1 ");
 $playerrow = mysql_fetch_array($playerquery);
 
 date_default_timezone_set("America/New_York");
 
 $clubmemberquery = mysql_query("SELECT clubid,playerid FROM clubmembership where playerid = ".$playerid." limit 1");
 $clubmemberrow = mysql_fetch_array($clubmemberquery);
 
 $clubquery = mysql_query("SELECT clubid,name,ownerid FROM club where clubid = ".$clubmemberrow["clubid"]." limit 1");
 $clubrow = mysql_fetch_array($clubquery);

 $itemrow = mysql_fetch_array(mysql_query("Select quantity from items where itemid = 6 and playerid = ".$playerid." limit 1"));
 
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
             
function jobname($jobid)
{
  
  if($jobid == "1")
  
  return "Street Sweeper";
  if($jobid == "2")
  return "Messenger";
  if($jobid == "3")
  return "Beggar";
  if($jobid == "0")
  return "Umemployed";              
}

  
?>
  
  
  <html>
  <head>
  <link rel="stylesheet" href="../styles/profile.css" type="text/css" />
  <title><?php echo $playerrow["name"] ."'s Profile";?></title>
  <script type="text/javascript" src="../js/jquery-1.8.2.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui-1.9.0.min.js"></script>
  
  
  </head>
  <body>
      <div id='load' ></div>
  <div id="content">
  <h1>
  <?php  
  echo $playerrow["name"] ."'s Profile";
  $statusrow = Query("Select status,description from playerprofile where playerid = ".$playerid." limit 1");
  ?></h1>
  
  <marquee><big ><div  ><?php echo $statusrow["status"]  ?></div> </big></marquee>
<?php
  if($playerid == $_SESSION['userid'])
  {
 
  echo "<center><input type='textbox' id='status' value = ''    /></center>";    
  }    
  
  ?>
  <div class="padding"></div>     
  
  <div class="box"><center>
  <br><br><br><br>
  <?php echo $statusrow["description"]; ?>
  
  <br><br><br><br>
  </center>
  </div>
  <?php
  if($playerid == $_SESSION['userid'])
  {
 
  echo "<center><br /><textarea id='desc'    ></textarea></center><br />";
  echo "<center><input type='button' value='submit' onclick='updatedesc();' /></center>";
  }
  ?>
  <div class="padding"></div> 
  
  <div id="info">
  
  <h2>Info</h2>
  <div class="column">
  <?php
  //<big>Member Since:</big> date <br>
  ?>
  <big>Last Time Logged In:</big>
  <?php
  $time = $userrow["loggedin"];
  echo "".$date = date("D, d M Y H:i:s", $time)."";
  ?>
  <br>
  <big>Last Seen At:</big> <?php
  echo $playerrow["location"] ." at <big>";
  echo $playerrow["la"] ." latitude </big>&<big> ";
  echo $playerrow["lo"] ." longitude </big>";
  ?>
  <BR>
  <?php
  //<big>Stable Name:</big> name <br>
  ?>
  <big>Social Class:</big> 
  <?php
  echo $playerrow["class"]
  ?>
  <BR> 
  <?php
  echo "<big>Net Worth:</big> ".$itemrow["quantity"]." <img width='25px' src='../images/coin2.png' /><br>";
  ?>
 
  <?php
//  echo jobname($playerrow["jobid"]);
  ?>
  <br>
  <big>Is a member of:</big> 
  <?php
  echo "<a href='clubprofile.php?clubid=".$clubrow["clubid"]."' >".$clubrow["name"]."</a>"; 
  ?>
  </div>
  
  
  </div><div class="padding"></div>
  <!--
  <div class="box">
  <h2>Friends</h2>
  
  <table border="1" width=100%>
  <tr>
  <th width=70%><big>Name</big></th>
  <th width=30%><big>Contact</big></th>
  </tr>
  <tr>
  <td width=70%><a href="">name (#)</a></td>
  <td width=30%>message</td>
  </tr>
  <tr>
  <td width=70%><a href="">name (#)</a></td>
  <td width=30%>message</td>
  </tr>
  <tr>
  <td width=70%><a href="">name (#)</a></td>
  <td width=30%>message</td>
  </tr>
  
  </table>
  
  
  </div>
  <div class="padding"></div>
    -->

  <div id="horses">
  <h2>Horses</h2>
  <big>Total number of horses:</big> #<br>
  <center>
  <table border="1" width=100%>
<tr>
 
<th><big>Image</big></th>
<th><big>Name</big></th>
<th><big>Breed</big></th>
<th><big>Gender</big></th>
<th><big>Color</big></th>
</tr>  
  <?php
  $horsesquery = mysql_query("SELECT horseid,name,breed,sex,color FROM horse where ownerid = ".$playerid." ");
  while ($horsesrow = mysql_fetch_array($horsesquery))
  {
echo "<tr>";
echo "<td><a href='horseprofile.php?horseid=".$horsesrow["horseid"]."'><img src=../images/horse/".horseimage($horsesrow["breed"],$horsesrow["color"])." width=100px></a>";
echo "<td><a href='horseprofile.php?horseid=".$horsesrow["horseid"]."'>".$horsesrow["name"]."</a></td>";
echo "<td>".$horsesrow["breed"]."</td>";
echo "<td>".$horsesrow["sex"]."</td>";
echo "<td>".$horsesrow["color"]."</td>";
echo "</tr>";
  }
  
  
  
  ?>
  
</table>
</center>
  
  <br><br><br><br><br>
  </div>
  
  </div>
  </body>
  <script type="text/javascript" src="js/profile.js"></script>
  </html>


































