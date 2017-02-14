if( isset($_SESSION['authorized']) and $_SESSION['authorized']==true ){
	
	date_default_timezone_set("Europe/Paris");
	$date = date("Y-m-d");
	$heure = date("H:i:s");
	$heurefin = date("H:i:s", time()+3600);
	$dateheure= $date." ".$heure;
	$dateheurefin= $date." ".$heurefin;

	***HIDDEN LDAP ACCESS DATA***

	$link = mysql_connect("xxx", "xxx", "xxx") or die("Impossible de se connecter : " . mysql_error()); mysql_select_db("wordpress");

/*
*	Vérification des conditions et mise à jour des dates du certificat
*/
	
	if(isset($_POST['datedeb'])and!empty($_POST['datedeb']) and isset($_POST['datefin'])and !empty($_POST['datefin']))
		if(strtotime($_POST['datedeb'])<strtotime($_POST['datefin']))
		{
			$datedeb = date("Y-m-d",strtotime($_POST['datedeb']));
			$datefin = date("Y-m-d",strtotime($_POST['datefin']));
			$req = "UPDATE xxx SET xxx ='".$datedeb." 00:00:00' ,COL_DT_FIN_CERT='".$datefin." 00:00:00' 
					where xxx=".$_POST['idcert'];
					$res = mysql_query($req,$link)or die(mysql_error());
		}
		else
		{
			echo '<p style="color:#FFFFFF">Echec de la mise à jour du certificat ! La date de fin doit être postérieur à la date de début.</p>';
		}
	
	/*
	* Recherche d'un collaborateur dans l'active directory
	*/
	if(isset($_POST["nom"]) and !empty($_POST["nom"]))
	{	
		
		if (preg_match("/[^a-zàâçéèêëîïôûùüÿñæœ .-]+$/i", $_POST["nom"]))
		{
			echo '<script type="text/javascript">
				jQuery(document).ready(function($){
					$("#nom").val("");
				});
			</script>';
			echo '<p style="color:#FFFFFF">Les caractères spéciaux ne sont pas tolérés.</p>';
		}
		else { 
			
					echo '<table id="MyTable1" border="1" style="display:block; height:310px; width: 250px; border-width:2px; border: 2px solid white; overflow-y:scroll; text-align: left;">';
		echo '<style style="text/css">
				#MyTable1 tr:hover {
					background-color : #76C7F1;
				}
				#MyTable1 tr.selected table {
					background: none repeat scroll 0 0 #76c7f1;
					color: #000000;
			}

			 </style>';
		echo '<script type ="text/javascript">			
				jQuery(document).ready(function($){
					  $("#MyTable1 tr").click(function(){
						if ($(this).hasClass("selected")){
							$(this).removeClass("selected");						
						}else{
							$(this).addClass("selected").siblings().removeClass("selected");
						}
					});

					$(".mypost").on("click",function(){
						$.ajax({
							url: "../ajout-correctifs/",
							type: "POST",
							data: { selectedcoll: $("#MyTable1 tr.selected input").val(), setid: true},
							success: function(response){	
									window.location = "../ajout-correctifs/";
							},
							error: function(){
								  alert("POST METHOD ERROR : DATA NOT POSTED - IMMINENT PAGE RELOAD..");
								  window.location = "../ajout-correctifs/";
							}
						});
					});


				});
			  </script>';

			$query = "sn=".htmlspecialchars($_POST["nom"]);
			if( strrpos($query,"*") != false)
			{
				$query = str_replace("*","",$query);
			}
			$query=$query.'*';
			echo '<tbody>';

			//Recherche sur l'active directory PRHQ
			$conn=ldap_connect($ldapServerPRHQ);
			if ($conn)
				if(ldap_set_option($conn, LDAP_OPT_REFERRALS, 0))
					if (ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3))
						if(ldap_bind($conn,$rdnPRHQ,$mdpPRHQ))
						{
							$result=ldap_search($conn, $baseDNPRHQ, $query);
							ldap_sort($conn,$result,"sn");
							$info = ldap_get_entries($conn, $result);
							if(count($info) >1){
								for($i=0;$i<count($info)-1;$i++)
								{
									echo '<tr style="white-space: nowrap;">';
									$entity = "PRHQ";
									if($info[$i]["mail"][0] and $info[$i]["department"][0]){ 
										$val = $info[$i]["sn"][0]."///".$info[$i]["givenname"][0]."///".$info[$i]["mail"][0]."///".$info[$i]["telephoneNumber"][0]."///".$entity;
										echo '<td><table border=0><tr><td style="color:#FFFFFF; white-space: nowrap;"> <input type="hidden" value="'.$val.'" style="color:#FFFFFF;text-align: left;" />'.$info[$i]["sn"][0].' '.$info[$i]["givenname"][0].'</td></tr><tr><td style="color:#FFFFFF; white-space: nowrap;"> <input type="hidden" value="'.$val.'" style="color:#FFFFFF;text-align: left;" />'.$info[$i]["mail"][0].'</td></tr></table></td>';
										echo '</tr>';
									}
								}
							}
						}
			ldap_close($conn);

			//Recherche sur l'active directory EMEA
			$conn=ldap_connect($ldapServerEMEA);
			if ($conn)
				if(ldap_set_option($conn, LDAP_OPT_REFERRALS, 0))
					if (ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3))
						if(ldap_bind($conn,$rdnEMEA,$mdpEMEA))
						{
							$result=ldap_search($conn, $baseDNEMEA, $query);
							ldap_sort($conn,$result,"sn");
							$info = ldap_get_entries($conn, $result);
							if(count($info) >1){
								for($i=0;$i<count($info)-1;$i++)
								{
									echo '<tr style="white-space: nowrap;">';
									$entity = "EMEA";
									if($info[$i]["mail"][0] and $info[$i]["department"][0]){ 
										$val = $info[$i]["sn"][0]."///".$info[$i]["givenname"][0]."///".$info[$i]["mail"][0]."///".$info[$i]["telephoneNumber"][0]."///".$entity;
										echo '<td><table border=0><tr><td style="color:#FFFFFF; white-space: nowrap;"> <input type="hidden" value="'.$val.'" style="color:#FFFFFF;text-align: left;" />'.$info[$i]["sn"][0].' '.$info[$i]["givenname"][0].'</td></tr><tr><td style="color:#FFFFFF; white-space: nowrap;"> <input type="hidden" value="'.$val.'" style="color:#FFFFFF;text-align: left;" />'.$info[$i]["mail"][0].'</td></tr></table></td>';
										echo '</tr>';
									}
								}
							}
						}
			ldap_close($conn);

			//Recherche sur l'active directory MMPJ
			$conn=ldap_connect($ldapServerMMPJ);
			if ($conn)
				if(ldap_set_option($conn, LDAP_OPT_REFERRALS, 0))
					if (ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3))
						if(ldap_bind($conn,$rdnMMPJ,$mdpMMPJ))
						{
							$result=ldap_search($conn, $baseDNMummpj, $query);
							ldap_sort($conn,$result,"sn");
							$info = ldap_get_entries($conn, $result);
							if(count($info) >1){
								for($i=0;$i<count($info)-1;$i++)
								{
									echo '<tr style="white-space: nowrap;">';
									$entity = "MMPJ";
									if($info[$i]["mail"][0] and $info[$i]["department"][0]){ 
										$val = $info[$i]["sn"][0]."///".$info[$i]["givenname"][0]."///".$info[$i]["mail"][0]."///".$info[$i]["telephoneNumber"][0]."///".$entity;
										echo '<td><table border=0><tr><td style="color:#FFFFFF; white-space: nowrap;"> <input type="hidden" value="'.$val.'" style="color:#FFFFFF;text-align: left;" />'.$info[$i]["sn"][0].' '.$info[$i]["givenname"][0].'</td></tr><tr><td style="color:#FFFFFF; white-space: nowrap;"> <input type="hidden" value="'.$val.'" style="color:#FFFFFF;text-align: left;" />'.$info[$i]["mail"][0].'</td></tr></table></td>';
										echo '</tr>';
									}
								}
							}
						}
			ldap_close($conn);
			echo '</tbody>';
			echo '</table>';
			echo '<input type="submit" class="mypost fusion-button button-flat button-round button-xlarge button-blue button-1" style="margin-left: 300px; margin-top: -300px;" value="Valider" /> ';
			echo '<input type="hidden" id="authorized" name="authorized" value="true" />';
		}
		
	}

	if( isset($_POST["selectedcoll"]) and !empty($_POST["selectedcoll"]) or isset($_GET["mail"]) )
	{		
		if(isset($_GET["mail"]))
			{
				$mail = htmlspecialchars($_GET["mail"]);
			}
		
			list ($name,$surname,$mail,$tel,$entity) = split("///",$_POST["selectedcoll"]);
			$req = "SELECT * FROM xxxxx WHERE COL_EMAIL='".$mail."'";
			$res = mysql_query($req,$link)or die(mysql_error());
			if(mysql_num_rows($res)==0)
			{
				$req = "INSERT INTO xxxxx (xxx,xx,xx,xx,xx,xx,xx,xx,xx,xx,xxx,xx) 
				VALUES ('1','".$name."','".$surname."','".$mail."','".$tel."','".$dateheure."',1,1,1,'".$entity."','2016-01-01','2016-01-01')";
				$res = mysql_query($req,$link)or die(mysql_error());
			}
		
		$req = "SELECT * 
				FROM xxxx 
				WHERE xxx='".$mail."'";
		$res = mysql_query($req,$link)or die(mysql_error());
		while ($row = mysql_fetch_array($res, MYSQL_NUM))
		{
			$idcoll=$row[0];
			$name=strtoupper($row[2]);
			$surname=$row[3];
			$mail=$row[4];
			$tel=$row[5];
			list($datedcert, $heuredcert) = split(" ", $row[9]);
			list($datefcert, $heurefcert) = split(" ", $row[10]);
		}
		
		echo '<table width="100%"><thead></thead><tbody><form action="../ajout-correctifs/" method="POST">
			<input type="hidden" name="nomprenom" value="'.$surname.' '.$name.'">
			<tr><td style="color:#FFFFFF"><strong>Prénom : </strong>'.$surname.'</td></tr>
			<tr><td style="color:#FFFFFF"><strong>Nom : </strong>'.$name.'</td>
			<tr><td style="color:#FFFFFF"><strong>Mail : </strong>'.$mail.'</td></tr>
			<tr><td style="color:#FFFFFF"><strong>Certificat : </strong>';
		if($date>$datefcert)
		{
			echo '<strong style="color:red">'.date('d-m-Y',strtotime($datefcert)).'</strong><a href="../modifier-certificat/?id='.$idcoll.'&from=correctif" style="color:#e1e1e1"> Modifier</a></td></tr>';
		}
		else
		{
			echo '<span style="color:green">'.date('d-m-Y',strtotime($datefcert)).'</span><a href="../modifier-certificat/?id='.$idcoll.'&from=correctif" style="color:#e1e1e1"> Modifier</a></td></tr>';
		}
		$date = date("Y-m-d",time());
		echo '<tr><td><input type="hidden" name="mail" value="'.$mail.'">
			<strong style="color: #e1e1e1;">Date de présence: </strong><input id="dateheure" name="dateheure" type="datetime-local" value="'.$date.'T12:00"/></br></br>
			<select style="max-width: 180px; min-width: 180px;" name="log">';
		$requete = "SELECT xxx,xx 
					FROM xxx
					WHERE xxx = 0";
		$resultat = mysql_query($requete,$link);
		while ($row = mysql_fetch_array($resultat, MYSQL_NUM))
		{ 
			echo '<option value="'.$row[0].'">'.$row[1].'</option>';
		}
		echo '</select>';
		echo '<input class="fusion-button button-flat button-round button-xlarge button-blue button-1" type="submit" value="Valider" />';
		echo '<input type="hidden" id="authorized" name="authorized" value="true" />
		</form></tbody></table>';
	}

	if(isset($_POST["mail"]))
	{
		if(isset($_POST["dateheure"]))
		{
			$heure_enreg = date("Y-m-d H:i:s",strtotime($_POST["dateheure"]));
			
			$date_enr = date('d-m-Y H:i:s',strtotime($_POST["dateheure"]));
			if(preg_match("#^(((0[1-9]|[12]\d|3[01])[\/\.-](0[13578]|1[02])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|[12]\d|30)[\/\.-](0[13456789]|1[012])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|1\d|2[0-8])[\/\.-](02)[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((29)[\/\.-](02)[\/\.-]((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])))$#",$date_enr))
			{
				if( strtotime($_POST["dateheure"]) < date(time()))
				{
					$h = date("H",strtotime($_POST["dateheure"]));
					$req = "SELECT * 
						FROM xxxx
						WHERE xxx='".$_POST["mail"]."'
						and DATE(xxx) = '".date("Y-m-d",strtotime($_POST["dateheure"]))."' 
						and (((HOUR(xxx) >= 12 and HOUR(xxx) < 14 and ".$h." >= 12 and ".$h." < 14)) or
						(HOUR(xxx) >= 18 and HOUR(xxx) < 20 and ".$h." >= 18 and ".$h." < 20))";
					
					$res = mysql_query($req,$link)or die(mysql_error());
					if(mysql_num_rows($res)==0)
					{
						$requete = "INSERT 
								INTO xxxx (xxx,xxx,xxx,xx) 
								VALUES (1,'".$heure_enreg."','".$_POST["log"]."','".$_POST["mail"]."')";
						$res = mysql_query($requete,$link);
						$msg= "Collaborateur enregistré.\n";
					}
					else
					{
						$msg= "Echec de mise à jour! Collaborateur déja inscrit dans ce crénau.";
					}
				}
				else
				{
					$msg= "Echec de mise à jour! Vous ne pouvez pas inscrire un participant pour une date future.";
				}
			}
			else
			{
				$msg= "Echec de mise à jour! Le format de la date est incorrect.";
			}
			echo '<h4 style="color:#FFFFFF">'.$msg.'<h4>';
		}
	}
	
}

else{
	echo '<p style="text-align: center;"><strong style="color: #ffffff;" title="choice "><a href="../acces-restreint?redirect=ajout-correctifs"  style="color: #ffffff;">Page protégée</strong></p>';
}
