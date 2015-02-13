                                                                <?php
require 'Slim/Slim.php';
error_reporting(1);
header_remove(); 
ob_end_clean();
header("Connection: close");
$DEBUG=0;
$app = new Slim();
$app->get('/users', 'getUsers');
$app->get('/users/:id', 'getUser');
$app->post('/login', 'loginUser');
$app->get('/flats', 'getflats');
$app->get('/flats/:name', 'getflat');
$app->post('/addvisitor','addvisitor');
$app->post('/inappvisitor','inappvisitor');
$app->post('/visitordetail', 'getvisitordetail');
$app->post('/visitdetail', 'visitordetail');
$app->put('/checked', 'checked');
$app->run();

function getUsers() 
{
	$sql = "select * FROM security WHERE type='security' ORDER BY id";	
 		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_ASSOC);		

		
		 if($wines)
		 {
		     echo json_encode(array("status"=>"true",$wines));
		    
	         }
		else
		{
			echo json_encode(array("status"=>"false"));
		}

		$db = null;
		 die;
}



function getUser($id) 
{
	$sql = "select * FROM security WHERE id=".$id." AND type='security' ORDER BY id";

		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$db = null;
		if($wines)
		  {
		     echo json_encode(array("status"=>"true",$wines));
                  }
		 else
		  {
			echo json_encode(array("status"=>"false"));
		  }
		   die;
}

function getflats() 
{               
	     $res=array();
	   $sql = "SELECT id as ownerid,name as ownername,gender,age FROM `flatowners` ORDER BY id";
                try{ 
		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
           foreach($wines as $val)
                       { 
$uid=$val['ownerid'];
                            $sql1="Select id as Flatid,flatno,floorno FROM flatinfo WHERE user_id=$uid";
                            $db = getConnection();
		             $stmt = $db->query($sql1);  
		             $winess = $stmt->fetchAll(PDO::FETCH_ASSOC);
                             $val['flats']=$winess;
                             $winesx[]=$val;
                          
                       }
                              echo json_encode(array("status"=>"true","data"=>$winesx));
		} catch(PDOException $e) {
echo json_encode(array("status"=>"false"));
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
 die;
}


function getflat($id) 
{                $res=array();
	   $sql = "SELECT a.flatno,a.floorno,a.user_id,b.name as 'Owner name',b.gender,b.age,b.image  FROM `flatowners` as b INNER JOIN flatinfo as a ON a.user_id=b.id WHERE b.name like '%$id%'";
                 try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
		     echo json_encode(array("status"=>"true",$wines));
                  }
		 catch(PDOException $e) 
                     {
echo json_encode(array("status"=>"false"));
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
              }
 
}


function loginUser()
{
$request = Slim::getInstance()->request();
$user = json_decode($request->getBody());
//echo "<pre>";print_r($user);die;
$pas=$user->password;
$sql = "select id,username,password from security WHERE username=:username and  password=:password";
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("username", $user->username);
		$stmt->bindParam("password",$pas);
		$stmt->execute();
		$usern=$stmt->fetchAll(PDO::FETCH_ASSOC);	
		$db = null;
		if($usern)
		  { 
		     echo json_encode(array("status"=>"true",$usern[0]['id']));
	          }
		 else
		 {
			 echo json_encode(array("status"=>"false"));
		 } 
	
}


function addvisitor()
{
$request = Slim::getInstance()->request();
$user = json_decode($request->post('json'));
//print_r($user);

 move_uploaded_file($_FILES["driver_image"]["tmp_name"],"../uploads/images/".$_FILES["driver_image"]["name"]);
 move_uploaded_file($_FILES["idproof"]["tmp_name"],"../uploads/images/".$_FILES["idproof"]["name"]);
move_uploaded_file($_FILES["frontcar"]["tmp_name"],"../uploads/images/".$_FILES["frontcar"]["name"]);
move_uploaded_file($_FILES["backcar"]["tmp_name"],"../uploads/images/".$_FILES["backcar"]["name"]);
move_uploaded_file($_FILES["carplate"]["tmp_name"],"../uploads/images/".$_FILES["carplate"]["name"]);


 $drv_img=$_FILES['driver_image']['name'];
 $idproof=$_FILES['idproof']['name'];
$front_img=$_FILES['frontcar']['name'];
$back_img=$_FILES['backcar']['name'];
$plate=$_FILES['carplate']['name'];
$created=date('Y-m-d H:i:s');
$status="0";
 $sql = "INSERT INTO visitors(flat_no,driver_image,idproof_pic,frontcar_pic,backcar_pic,carplate_pic,entrytime,peoples,type,created_date,status,user_id) VALUES (:flat_no, :driver_image , :idproof_pic , :frontcar, :backcar_pic, :carplate_pic, :entrytime, :No_of_people,:type,:created_date,:status,:user_id)";


	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("flat_no", $user[0]->Flat_no);
		$stmt->bindParam("type", $user[0]->type);
		$stmt->bindParam("No_of_people", $user[0]->No_of_people);
                $stmt->bindParam("entrytime", $user[0]->time);      

		$stmt->bindParam("driver_image", $drv_img);
                $stmt->bindParam("idproof_pic", $idproof);
                $stmt->bindParam("frontcar", $front_img);
                $stmt->bindParam("backcar_pic", $back_img);
                $stmt->bindParam("carplate_pic", $plate);
                 $stmt->bindParam("created_date", $created);
       $stmt->bindParam("user_id", $user[0]->user_id);
              $stmt->bindParam("status", $status);
		$stmt->execute();
                $ids=$db->lastInsertId();
		$db = null;
		echo json_encode(array("status"=>"true","visitorid"=>$ids));
	} catch(PDOException $e) {
echo json_encode(array("status"=>"false"));
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}

}




function inappvisitor()
{

$request = Slim::getInstance()->request();
$user = json_decode($request->post('json'));

 move_uploaded_file($_FILES["driver_image"]["tmp_name"],"../uploads/images/".$_FILES["driver_image"]["name"]);
 move_uploaded_file($_FILES["idproof"]["tmp_name"],"../uploads/images/".$_FILES["idproof"]["name"]);
move_uploaded_file($_FILES["frontcar"]["tmp_name"],"../uploads/images/".$_FILES["frontcar"]["name"]);
move_uploaded_file($_FILES["backcar"]["tmp_name"],"../uploads/images/".$_FILES["backcar"]["name"]);
move_uploaded_file($_FILES["carplate"]["tmp_name"],"../uploads/images/".$_FILES["carplate"]["name"]);


 $drv_img=$_FILES['driver_image']['name'];
 $idproof=$_FILES['idproof']['name'];
$front_img=$_FILES['frontcar']['name'];
$back_img=$_FILES['backcar']['name'];
$plate=$_FILES['carplate']['name'];
$id=$user[0]->visitor_id;
$status="1";
$updateddate=date('Y-m-d H:i:s');
 $sql = "INSERT INTO visitor_inapp(flat_no,driver_image,idproof_pic,frontcar_pic,backcar_pic,carplate_pic,type,status,visitor_id,peoples,updateddate) VALUES (:flat_no, :driver_image , :idproof_pic , :frontcar, :backcar_pic, :carplate_pic, :type, :status,:visitor_id,:peoples,:updateddate)";


	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("flat_no", $user[0]->Flat_no);
		$stmt->bindParam("type", $user[0]->type);        
                 $stmt->bindParam("driver_image", $drv_img);
                $stmt->bindParam("idproof_pic", $idproof);
                $stmt->bindParam("frontcar", $front_img);
                $stmt->bindParam("backcar_pic", $back_img);
                $stmt->bindParam("carplate_pic", $plate);
                $stmt->bindParam("visitor_id", $user[0]->visitor_id);
 $stmt->bindParam("peoples", $user[0]->no_of_people);
 $stmt->bindParam("updateddate", $updateddate);
              $stmt->bindParam("status", $status);
		$stmt->execute();
  $sql1 = "UPDATE visitors SET status='9',updateddate='$updateddat' WHERE id='$id'";
$stmt2 = $db->prepare($sql1);  


$stmt2->execute();
// $stmt2->debugDumpParams();


        	$db = null;
		echo json_encode(array("status"=>"true","message"=>"successfully marked as inappropriate"));
	} catch(PDOException $e) {
echo json_encode(array("status"=>"false"));
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}

}


function getvisitordetail() 
{
//6
	$request = Slim::getInstance()->request();
$user = json_decode($request->getBody());
$type=$user[0]->type;
$flatno=$user[0]->flat_no;
$name=$user[0]->resident_name;

  $sql = "SELECT a.*,b.*  FROM `visitors` as b INNER JOIN flatinfo as a INNER JOIN flatowners as c ON c.id=a.user_id AND a.flatno=b.flat_no WHERE b.type='$type' AND b.flat_no='$flatno' AND c.name='$name' AND b.status=0";
		try {
                $db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("b.type", $type);
		$stmt->bindParam("b.flat_no",$flatno);
                $stmt->bindParam("c.name",$name);
		$stmt->execute();
		$usern=$stmt->fetchAll(PDO::FETCH_ASSOC);	//echo "<pre>";print_r($usern[0]);die;
		$db = null;
$url="http://netset.internetoffice.co.in/visitor/public/uploads/images/";


		
foreach($usern as $val)
{
$val['driver_image']=$url.$val['driver_image'];
$val['idproof_pic']=$url.$val['idproof_pic'];
$val['frontcar_pic']=$url.$val['frontcar_pic'];
$val['backcar_pic']=$url.$val['backcar_pic'];
$val['carplate_pic']=$url.$val['carplate_pic'];

$datax[]=$val;
}



		     echo json_encode(array("status"=>"true","data"=>$datax));
	        } catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}

	
}

function visitordetail() 
{
	$request = Slim::getInstance()->request();
$user = json_decode($request->getBody());
$id=$user[0]->id_of_image;
$sql = "SELECT * FROM `visitors` WHERE id='$id'";
		try {
                $db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("b.type", $type);
		$stmt->bindParam("b.flat_no",$flatno);
                $stmt->bindParam("c.name",$name);
		$stmt->execute();
		$usern=$stmt->fetchAll(PDO::FETCH_ASSOC);	
		$db = null;
		 
		     echo json_encode(array("status"=>"true",$usern[0]));
	        } catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}

	
}

function checked() 
{
	$request = Slim::getInstance()->request();
	$user = json_decode($request->getBody());
        $id=$user[0]->id;
$updateddate=date("Y-m-d H:i:s");
	 $sql = "UPDATE visitors SET status='2' , updateddate= '$updateddate' WHERE id='$id'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$db = null;
		 echo json_encode(array("status"=>"true","message"=>"succussfully checked")); 
	} catch(PDOException $e) {
 echo json_encode(array("status"=>"false","message"=>"unsuccussfully checked")); 
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

function getConnection() {
	$dbhost="localhost";
	$dbuser="netsetin_visitor";
	$dbpass='+kya9]g$ydEE';
	$dbname="netsetin_visitorapp";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}
function  debug($data){
global $DEBUG;
if($DEBUG){
echo "<pre>";
print_r($data);
}
}
?>
                            
                            
                            
                            

                            

                            
                            
                            
                            
                            