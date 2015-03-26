<?php
$serveur = "localhost";
$login = "root";
$mot_de_passe = "brides";
echo "<br>Nom de la base : ".$nom_de_la_base = "pmqvision4";
echo "<br>Nom de la table : ".$nom_de_la_table= "personne_old";

$link = mysql_connect($serveur, $login, $mot_de_passe);
mysql_select_db($nom_de_la_base, $link);
$result = mysql_query('SELECT * FROM '.$nom_de_la_table);
while ($ligne = mysql_fetch_array($result)) 
{
	
$date_tableau = explode('-',$ligne["personne_date_naissance"]);

if($date_tableau[2] == 00)
{
	$datebase=$date_tableau[0].'-01-01';
	$personne_id=$ligne["personne_id"];
	$result2 = mysql_query("UPDATE personne SET personne_date_naissance   ='".$datebase."' WHERE personne_id='".$personne_id."'", $link);
	
}else{
	$personne_id=$ligne["personne_id"];
	$result2 = mysql_query("UPDATE personne SET personne_date_naissance   ='".$ligne["personne_date_naissance"]."' WHERE personne_id='".$personne_id."'", $link);
}

}