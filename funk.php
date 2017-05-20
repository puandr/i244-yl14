<?php

function connect_db(){
	global $connection;
	$host="localhost";
	$user="test";
	$pass="t3st3r123";
	$db="test";
	$connection = mysqli_connect($host, $user, $pass, $db) or die("ei saa ühendust mootoriga- ".mysqli_error());
	
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Ei saanud baasi utf-8-sse - ".mysqli_error($connection));
	
}

function logi(){
	// siia on vaja funktsionaalsust (13. nädalal)
	global  $connection;
	
	if (!empty($_SESSION['user'])) {
		header("Location: ?page=loomad");
	} else {		
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			if ($_POST['user'] != "" && $_POST['pass'] != "") {
				$kasutaja = mysqli_real_escape_string($connection, $_POST['user']);
				$parool = mysqli_real_escape_string($connection, $_POST['pass']);
				$query = "SELECT id, roll FROM 10162828_kylastajad WHERE username = '$kasutaja' AND passw = SHA1('$parool')";
				$result = mysqli_query($connection, $query);					
				if (mysqli_num_rows($result)) {
					$_SESSION['user'] = $_POST['user'];
					$rida = mysqli_fetch_assoc($result);
					$_SESSION['roll'] = $rida['roll'];
					$id = $rida['id'];
					header("Location: ?page=loomad");
					//õigem oleks siduda visiitide arvu külastaja ID-ga, aga väikse andmebaasi puhul sobib ka nime jaärgi
					$query3 = "UPDATE 10162828_kylastajad SET visits = visits + 1 WHERE username = '$kasutaja';";
					$result3 = mysqli_query($connection, $query3) or die("$query3 - ".mysqli_error($connection));
				} else {
					$errors[] = "Vale kasutajanimi või parool";
				}
			} else {
				$errors[] = "Palun sisestage kasutajanimi ja parool";
			}
		}
	}
	
	include_once('views/login.html');
}



function logout(){
	$_SESSION=array();
	session_destroy();
	header("Location: ?");
}

function kuva_puurid(){
	// siia on vaja funktsionaalsust
	global $connection;
	
	if (!empty($_SESSION["user"])) {
		$puurid = array();
		//$puuri_nr = array();
		
		$query = "SELECT DISTINCT puur FROM 10162828_loomaaed";
		$result = mysqli_query($connection, $query) or die("$query - ".mysqli_error($connection));
			
		//$ridade_arv = mysqli_num_rows($result);
		
		while ($ajutine = mysqli_fetch_assoc($result)) {
		
			$query_2 = "SELECT * FROM 10162828_loomaaed WHERE puur=".mysqli_real_escape_string($connection, $ajutine['puur']);
			$result_2 = mysqli_query($connection, $query_2) or die("$query - ".mysqli_error($connection));
			//print_r($result_2);
			//echo "<p>";
			while ($rida = mysqli_fetch_assoc($result_2)) {
				$puurid[$ajutine['puur']][] = $rida;
			}			
		}		
	} else {
		header("Location: ?page=login");
	}	
	/*
	echo "<p><pre>";
	print_r($puurid);
	echo "</pre>";
	*/
	include_once('views/puurid.html');
	
}


function lisa(){
	// siia on vaja funktsionaalsust (13. nädalal)
	
	global $connection;
	
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	} elseif ($_SESSION['roll'] == 'user') {
		header("Location: ?page=loomad");
	} else {
		//echo "<p>";
		//print_r($_SERVER['REQUEST_METHOD']);
		//echo "</p>";
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			if ($_POST['nimi'] == "" || $_POST['puur'] == "") {
				$errors[] = "Nimi või puur on sisestamata";
			} elseif (upload("liik") == ""){
				$errors[] = "Faili saatmine nurjus";
			} else {
				upload('liik');
				$nimi = mysqli_real_escape_string($connection, $_POST['nimi']);
				$puur = mysqli_real_escape_string($connection, $_POST['puur']);
				$liik = mysqli_real_escape_string($connection, substr($_FILES['liik']['name'], 0, -4));
				$query = "INSERT INTO 10162828_loomaaed(id, nimi, puur, liik) VALUES (NULL, '$nimi', '$puur', '$liik')";
				$result = mysqli_query($connection, $query);
				
				if (mysqli_insert_id($connection)) {
					header("Location: ?page=loomad");
				} else {
					header("Location: ?page=loomavorm");
				}
			}
		}
		
		
	}
	include_once('views/loomavorm.html');
	
}


function muuda(){
	// siia on vaja funktsionaalsust (13. nädalal)
	
	global $connection;
	
	if (empty($_SESSION['user'])) {
		header("Location: ?page=login");
	} elseif ($_SESSION['roll'] == 'user') {
		header("Location: ?page=loomad");
	} 
	if (isset($_POST['id'])) {
		//echo "<p>";
		//print_r($_SERVER['REQUEST_METHOD']);
		//echo "</p>";
		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			if ($_POST['nimi'] == "" || $_POST['puur'] == "") {
				$errors[] = "Nimi või puur on sisestamata";
			} elseif (upload("liik") == ""){
				$errors[] = "Faili saatmine nurjus";
			} elseif ($_POST['id'] == ""){
				header("Location: ?page=loomad");
			} else {
				//upload('liik');
				$id = mysqli_real_escape_string($connection, $_POST["id"]);
				$loom = hangi_loom($id);
				$nimi = mysqli_real_escape_string($connection, $_POST["nimi"]);
				$puur = mysqli_real_escape_string($connection, $_POST["puur"]);				
				$liik = mysqli_real_escape_string($connection, substr($_FILES['liik']['name'], 0, -4));
				
				if (upload("liik")) {
					$liik = mysqli_real_escape_string($connection, upload("liik"));
				} else {
					$liik = $loom['liik'];
				}
				$query5 = "UPDATE 10162828_loomaaed SET nimi = '$nimi', puur = '$puur', liik = '$liik' WHERE id = '$id'";
				$result = mysqli_query($connection, $query5);
				header("Location: ?page=loomad");
				
				if (mysqli_insert_id($connection)) {
					header("Location: ?page=loomad");
				} else {
					header("Location: ?page=loomavorm");
				}
			}
		} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$id = mysqli_real_escape_string($connection, $_GET["id"]);
			if ($id == "") {
				header("Location: ?page=loomad");
			} else {
				$loom = hangi_loom($id);
			}
		}
		
		
	}
	include_once('views/loomavorm.html');
	
}

function upload($name){
	$allowedExts = array("jpg", "jpeg", "gif", "png");
	$allowedTypes = array("image/gif", "image/jpeg", "image/png","image/pjpeg");
	$extension = end(explode(".", $_FILES[$name]["name"]));

	if ( in_array($_FILES[$name]["type"], $allowedTypes)
		&& ($_FILES[$name]["size"] < 100000)
		&& in_array($extension, $allowedExts)) {
    // fail õiget tüüpi ja suurusega
		if ($_FILES[$name]["error"] > 0) {
			$_SESSION['notices'][]= "Return Code: " . $_FILES[$name]["error"];
			return "";
		} else {
      // vigu ei ole
			if (file_exists("pildid/" . $_FILES[$name]["name"])) {
        // fail olemas ära uuesti lae, tagasta failinimi
				$_SESSION['notices'][]= $_FILES[$name]["name"] . " juba eksisteerib. ";
				return "pildid/" .$_FILES[$name]["name"];
			} else {
        // kõik ok, aseta pilt
				move_uploaded_file($_FILES[$name]["tmp_name"], "pildid/" . $_FILES[$name]["name"]);
				return "pildid/" .$_FILES[$name]["name"];
			}
		}
	} else {
		return "";
	}
}

function hangi_loom($id) {
	global $connection;
	$query4 = "SELECT * FROM 10162828_loomaaed WHERE id={$id}";
	$result = mysqli_query($connection, $query4) or die("midagi läks valesti");
	if (mysqli_num_rows($result) >= 1) {
		$loom =mysqli_fetch_assoc($result);
		return $loom;
	} else {
		header("Location: loomaaed.php?page=loomad");
	}
}

?>