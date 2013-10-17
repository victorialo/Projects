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
 



 if(isset($_GET["horseid"]))
 {
 
 $horseid = $_GET["horseid"];
 
 }
 else
 {
 $horseid = 1;
 }


 $horsequery = mysql_query("SELECT * FROM horse where horseid = ".$horseid." limit 1");
 $horserow = mysql_fetch_array($horsequery);
 
// $horsestat = Query("Select * from horsestats where horseid = ".$horseid." limit 1 ");
 
 
 date_default_timezone_set("America/New_York");

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
  <title>  <?php  echo $horserow["name"];?> 's profile </title>
   <script type="text/javascript" src="../js/jquery-1.8.2.min.js"></script>
  <script>
     
  function Sendmessage(umsg,id,func,Param1,Param2,Param3,Param4,Param5,Param6,Param7)
{
var url = "../input.php?umsg=" + umsg;

    
    if(id)
    {
    url += "&id="+id;
    }
    if(Param1 != null){url += "&param1="+Param1;}
    if(Param2 != null){url += "&param2="+Param2;}
    if(Param3 != null){url += "&param3="+Param3;}
    if(Param4 != null){url += "&param4="+Param4;}
    if(Param5 != null){url += "&param5="+Param5;}
    if(Param6 != null){url += "&param6="+Param6;}
    if(Param7 != null){url += "&param7="+Param7;}  
 
      $.get(url);
      


}
  </script>
  </head>
  <body>
  
  <div id="content">
  <?php
    if(isset($_GET["healthhelp"]) || isset($_GET["staminahelp"]) || isset($_GET["energyhelp"]) || isset($_GET["agilityhelp"]) || isset($_GET["strengthhelp"]) || isset($_GET["obediencehelp"]))
  {include('profilehelp.php');return;}  
  echo    "<h1>";
  
  
  
    



  echo $horserow["name"];
  ?></h1>
  <center>
  
  
      <?php
      
  $horsesquery = mysql_query("SELECT horseid,name,breed,sex,color,la,lo,location,la2,lo2 FROM horse where horseid = ".$horseid." ");
  while ($horsesrow = mysql_fetch_array($horsesquery))
  {
  echo "<td><a href=''><img src=../images/horse/".horseimage($horsesrow["breed"],$horsesrow["color"])."></a>";
  }
  ?>
  <BR><BR>
  </center>
  
  
  <div class="box2">
  
  <h2>Basic Info</h2>
  
  <?php //<big>Last Time Logged In:</big>
  //$time = $userrow["loggedin"];
  //echo "".$date = date("D, d M Y H:i:s", $time).""; <br>
  
  ?>
  
  
  <big>Breed:</big> 
  <?php
  echo $horserow["breed"];
  ?>
  <br>
  <big>Gender:</big> 
  <?php
  echo $horserow["sex"];
  ?>
  <br>
  <big>Age:</big> 
  <?php
  echo $horserow["age"];
  ?>
  <br>
  <big>Weight:</big> 
  <?php
  echo $horserow["weight"];
  ?>
  <br>
  <big>Height:</big> 
  <?php
  echo $horserow["height"];
  ?>
  
 
  <br>
  <big>Owner: </big> 
  <?php
  
 
  $clubid = Query("Select clubid,name from club where clubid = ".$horserow["clubid"]." limit 1 ");
  
  if($clubid)
  {
  
  echo "<a href='clubprofile.php?clubid=".$clubid["clubid"]."'>".$clubid["name"]."</a>";
  
  }
  else
  {
  $name = Query("Select name from player where playerid = ".$horserow["ownerid"]." limit 1");
  echo "<a href='profile.php?playerid=".$horserow["ownerid"]."'>".$name["name"]."</a>";
  }
  
  
  
  ?>
  
  <br><BR>
  
  
  
  
  
  </div>
  
  <div class="box3">
  
  <h2>Stats</h2>
    <span style='float:left;margin:5px;'>
  <big><a href='horseprofile.php?healthhelp' >Health:</a></big> 
  <?php echo $horserow["health"] + $horserow["addhealth"]?>
 <br />
 <big><a href='horseprofile.php?energyhelp' >Energy:</a></big> <?php
  echo $horserow["energy"]
  ?>
    </span>
 
  <span style='float:left;margin:5px;'>
   <big><a href='horseprofile.php?strengthhelp' >Strength:</a></big>  
  <?php
  echo $horserow["strength"];
  ?>
  <br />
  <big><a href='horseprofile.php?staminahelp' >Stamina:</a></big>  
  <?php
  echo $horserow["stamina"]
  ?>
 <br />
   <big><a href='horseprofile.php?agilityhelp' >Agility:</a></big>  
  <?php
  echo $horserow["agility"];
  ?>
  
  </span>
  <span style='float:left;margin:5px;'>
  <big><a href='horseprofile.php?obediencehelp' >Obedience:</a></big>  
  <?php
//  echo $horsestat["obedience"];
  ?>
 <br />
  <big>Speed:</big> 
  <?php
 // echo $horsestat["speed"];
  ?>
  </span>
  <span style='float:left;margin:5px;'>
  <big>Satiation:</big> 0 
  <?php ?>
 <br />
  <big>Hydration:</big> 0
  <?php ?>
  </span>
  <span style='float:left;margin:5px;'>
  <big>Races:</big> 0
  <br />
  <big>First:</big> 0
  <br />
  <big>Second:</big> 0
  <br />
  <big>Third:</big> 0
  
  
  </span>
  <span style='float:left;margin:5px;'>
  <big>Winnings:</big> 
  <br /><br />
  <big>0 gold</big>
  </span>
  <span style='float:left;margin:5px;'>
  <big>Injuries:</big> 
  <br /><br />
  None
  <br /><br />
  <big>Total:</big> 
  <br /><br />
  0
  </span>
   <span style='float:left;margin:5px;'>
  <big>Previous Owners:</big> 
  
  
   </span>
  <span style='float:left;margin:5px;'>
    <a href="horseprofile.php?horseid="><b>Sire</b></a> x <a href="horseprofile.php?horseid="><b>Mare</b></a><BR><Br> 
  </span>
  <span style='float:left;margin:5px;'>
  <big>Offspring:</big> 
  
  
   </span>
  <br />
  
  </div>
      
      
      
      
   <div class="padding"></div><div class="padding"></div>
  
  
  <div class="box2">
  <h2>Current Location</h2>
  <?php
	if($horserow["location"] == "none")
	echo "<big>".$horserow["name"]."</big> is currently being used or ridden or tied or brushed";    
	else if($horserow["location"] == "stable" || $horserow["location"] == "stable2" || $horserow["location"] == "stable3") 
	echo " <big>".$horserow["name"]."</big> is currently at <big>".$horserow["la"]." </big>, <big>".$horserow["lo"]."</big> in the <big>Stable</big> at <big>".$horserow["la2"]."</big> , <big> ".$horserow["lo2"]."</big>  ";
	else
	echo " <big>".$horserow["name"]."</big> is currently in the <big>".$horserow["location"]."</big> at <big>".$horserow["la2"]." </big>, <big>".$horserow["lo2"]."</big>  ";


?>
<br /><br />
<?php
if($playerid == 1)
echo "<div onclick=Sendmessage(\"tphorse\",".$horseid.") >Teleport Horse</div>";  
?>
<br /><br />
  </div>
  
  <div id="horses">
  <h2>Description</h2>
  <BR><BR>
  
  <textarea rows="4" cols="50">

</textarea>
  
  






</table>

  
  <br><br><br><br><br>
  </div>
  
  </div>
  </body>
  </html>
