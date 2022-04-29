
<html><head>
<title>WEBAPP CAP-VO</title>
<meta http-equiv="Cache-control" content="no-cache">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="jquery.js" type="text/javascript"></script>
<link rel="icon" type="image/png" href="/capvo/img/logo.png"/>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="stylesheet" type="text/css" href="../dhtmlx/web.css"/>
<link rel="stylesheet" type="text/css" href="../dhtmlx/terrace.css"/>
<link rel="stylesheet" type="text/css" href="../dhtmlx/skyblue.css"/>
<link rel="stylesheet" type="text/css" href="../dhtmlx/dhtmlx.css"/>
<link rel="stylesheet" type="text/css" href="../dhtmlx/suite.css?v=6.4.1">
<link rel="stylesheet" type="text/css" href="../dhtmlx/index.css?v=6.4.1">

<script type="text/javascript" src="../dhtmlx/suite.js?v=6.4.1"></script>
<script type="text/javascript" src="../dhtmlx/dataset.js?v=6.4.1"></script>
<script type="text/javascript" src="../dhtmlx/dhtmlx.js"></script>


<style>
body {
  background-color: #FFFFFF;
font-family: sans-serif;
font-size: 8px;
}

.even{
    background-color:#DDDDDD;
}
.uneven{
    background-color:#EEEEEE;
}
.label-file {
		cursor:pointer;
		//color: #111111;
		font-weight: bold;
		//border: 1px solid #111111;
		width: 180px;
		height: 25px;
}
.label-file:hover{
	color:#333333;
}
* { box-sizing: border-box; }
body {
  font: 16px Arial;
}


.autocomplete {
  /*the container must be positioned relative:*/
  position: relative;
  display: inline-block;
}
input {
  border: 1px solid transparent;
  background-color: #ffffff;
  border: 1px solid #d4d4d4;
  padding: 3px;
  font-size: 12px;
}

	 
input[type=text] {
  background-color: #ffffff;
  border: 1px solid #d4d4d4;
  width: 145px;
}
input[type=submit] {
  background-color: DodgerBlue;
  color: #fff;
}
/*Modif TJO*/
input[disabled] {
  background-color: #e1dcfc;
  border: 1px solid #d4d4d4;
	 }
	 
.autocomplete-items {
  position: absolute;
  border: 1px solid #d4d4d4;
  border-bottom: none;
  border-top: none;
  z-index: 99;
  /*position the autocomplete items to be the same width as the container:*/
  top: 285px;
  left: 125px;
  right: 0;
}
.autocomplete-items div {
  padding: 4px;
  cursor: pointer;
  background-color: #EEEEEE;
  border-bottom: 1px solid #d4d4d4;
  width: 195px;
}
.autocomplete-items div:hover {
  /*when hovering an item:*/
  background-color: #e9e9e9;
}
.autocomplete-active {
  /*when navigating through the items using the arrow keys:*/
  background-color: DodgerBlue !important;
  color: #ffffff;
}

.Previsualisation img{
  height: auto;
  width: 340px;
}

.hidden {
	display: none;
}

</style>
</head>

<?php
setcookie("QUALITY","80", time()+699999); // 30 jours de validité
require __DIR__.'/vendor/autoload.php';
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once("../config.php");
$token="";
$pdo= new PDO($mysql_server,$mysql_user,$mysql_pass);

if ( $_POST['vehicule_id'] != '-' )
	{	
	setcookie("VEHICULE_ID", $_POST['vehicule_id'], time()+699999); // 30 jours de validité
	$_COOKIE['VEHICULE_ID']=$_POST['vehicule_id'];
	}

if ( $_POST['immatriculation'] != '-' && $_POST['immatriculation'] != '')
	{	
	$id=explode(":", $_POST['immatriculation']);
	//$sql="select v.vehicule_id from vehicule_couverts v, statut_cotation s, commentaires c ,marchand_vendeurs m where  m.marchand_id=c.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and v.immatriculation='".$_POST['immatriculation']."' and s.statut_id=v.statut_id and c.commentaire_type='marchand' order by v.date_couverture desc; "; 
	//$stm=$pdo->query($sql); 
	//$row=$stm->fetch(); 
	file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
	setcookie("VEHICULE_ID", $id[1], time()+699999); // 30 jours de validité
	$_COOKIE['VEHICULE_ID']=$id[1];
	}


if ( $_POST['connecter'] == 'Connecter' )
	{
	$sql="select vendeur_nom, vendeur_prenom from vendeurs where vendeur_email = lower('".$_POST['email']."') and vendeur_mdp ='".$_POST['mdp']."' ;";
	file_put_contents($logfilew,'SQL : '.$sql."\r\n",FILE_APPEND);
	$stm=$pdo->query($sql) ; 
	$row=$stm->fetch(); 
	//echo 'RET :'. $row[0];
	if ( $row[0] != '' )
		{
		$token=passgen2(30);
		//echo "set : ".$value;
		//setcookie("CAPVO_TOKEN", $token, time()+2592000); // 30 jours de validité
		setcookie("CAPVO_TOKEN", $token, time()+699999); // 30 jours de validité
		setcookie("USER_PROFILE", 'VENDEUR', time()+699999);
		$_COOKIE['CAPVO_TOKEN']=$token;
		$_COOKIE['USER_PROFILE']='VENDEUR';
		
		$sql1="update vendeurs set token_id = '".$token."' where vendeur_email = '".$_POST['email']."' and vendeur_mdp ='".$_POST['mdp']."' ;";
		$stm=$pdo->query($sql1) ; 
		file_put_contents($logfilew,$sql1."\r\n",FILE_APPEND); 
		}
	else
		{
		//Il S'agit d'un marchand
		$sql="select vendeur_nom, marchandvendeur_id from marchand_vendeurs v, marchands m where v.marchand_id=m.marchand_id and vendeur_email = lower('".$_POST['email']."') and  vendeur_statut ='actif' and marchand_capvo_provo in ('CAPVO-PROSVO','CAPVO','DISPOFLASH','DISPOFLASH-CAPVO','DISPOFLASH-CAPVO-PROSVO') and vendeur_mdp ='".$_POST['mdp']."' ;";
		file_put_contents($logfilew,'SQLM : '.$sql."\r\n",FILE_APPEND);
		$stm=$pdo->query($sql) ; 
		$row=$stm->fetch(); 
		if ( $row[0] != '' )
			{
			$token=passgen2(30);
			setcookie("CAPVO_TOKEN", $token, time()+699999); // 30 jours de validité
			setcookie("USER_PROFILE", 'MARCHAND', time()+699999);
			setcookie("CAPVO_ID", $row[1], time()+699999);
		 
			$_COOKIE['CAPVO_TOKEN']=$token;
			$_COOKIE['USER_PROFILE']='MARCHAND';
			$_COOKIE['CAPVO_ID']=$row[1];
		
			$sql1="update marchand_vendeurs set token_id = '".$token."' where vendeur_email = '".$_POST['email']."' and vendeur_mdp ='".$_POST['mdp']."' ;";
			$stm=$pdo->query($sql1) ; 
			file_put_contents($logfile,$sql1."\r\n",FILE_APPEND);
			}			
		}	
	}
	
//echo "QUAL :" .$_COOKIE['QUALITY']; // 30 jours de validité
?>
<script>
var mygrid_price, couverture_id;

let restore = $('meta[name=viewport]')[0];
if (restore) {
    restore = restore.outerHTML;
}
$('meta[name=viewport]').remove();
$('head').append('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');
if (restore) {
    setTimeout(() => {
        $('meta[name=viewport]').remove();
        $('head').append(restore);
    }, 100); // On Firefox it needs a delay > 0 to work
}


function autocomplete(inp, arr) {
  /*the autocomplete function takes two arguments,
  the text field element and an array of possible autocompleted values:*/
  var currentFocus;
  /*execute a function when someone writes in the text field:*/
  inp.addEventListener("input", function(e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) { return false;}
      currentFocus = -1;
      /*create a DIV element that will contain the items (values):*/
      a = document.createElement("DIV");
      a.setAttribute("id", this.id + "autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the DIV element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        /*check if the item starts with the same letters as the text field value:*/
        if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
          /*create a DIV element for each matching element:*/
          b = document.createElement("DIV");
          /*make the matching letters bold:*/
          b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
          b.innerHTML += arr[i].substr(val.length);
          /*insert a input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
          /*execute a function when someone clicks on the item value (DIV element):*/
              b.addEventListener("click", function(e) {
              /*insert the value for the autocomplete text field:*/
              inp.value = this.getElementsByTagName("input")[0].value;
              /*close the list of autocompleted values,
              (or any other open lists of autocompleted values:*/
              closeAllLists();
          });
          a.appendChild(b);
        }
      }
  });
  /*execute a function presses a key on the keyboard:*/
  inp.addEventListener("keydown", function(e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
        increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
        decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
  });
  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-active");
  }
  function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }
  function closeAllLists(elmnt) {
    /*close all autocomplete lists in the document,
    except the one passed as an argument:*/
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt != x[i] && elmnt != inp) {
      x[i].parentNode.removeChild(x[i]);
    }
  }
}
/*execute a function when someone clicks in the document:*/
document.addEventListener("click", function (e) {
    closeAllLists(e.target);
});
}

function showPhotos(val,ref) {
if ( val == '1' )
	{
	document.getElementById('photos').style.display = 'none';
	}
else
	{ 
	document.getElementById('photos').style.display = 'block';	
   }
}


function showCvtGrid(val,ref) {
if ( val == '1' )
	{

/*$(document).ready(()=>{
       $('button').on('click', ()=>{
           let e = $.Event('keypress');
           e.key = ' ';
	   })
});*/

	document.getElementById('price_cont').style.display = 'none';
	console.log('SEND '+getCookie('CAPVO_ID'));
	sendSock('updateMarchand','reload|'+getCookie('CAPVO_ID'));
	//pausecomp(1000);
	//setInterval(window.location.reload(true),5000);
	window.location.reload(true);
	}
else
	{ 
		let request=new XMLHttpRequest();
		request.open("GET",'./get_updatecvt.php');
		request.onload = function(e) 
			{
			var ret = request.response; // n'est pas responseText
			var value=ret.split('|');
			//console.log(' HEURE : '+value[0]);
			if ( value[0] == 'Termin&eacute;' )
				{
					document.getElementById('addcvt').style.display = 'none';
					//document.getElementById("addcvt").disabled = true;
					
					console.log('HEURE 2 : '+value[0]);
				}
			//document.getElementById("statut_cvt").innerHTML=value[1];
			//document.getElementById("statut_capvo").innerHTML=value[2];
			}
		request.send();
		pausecomp(400);
	document.getElementById('price_cont').style.display = 'block';	
   }
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length,c.length);
        }
    }
    return "";
}

// NB Image à charger au minimum //
var active_button=2;
var active_variable=1;


function getPrice(id_veh) {
	
document.getElementById("prixpart").innerHTML="<span style='font-family:arial,serif;font-size:10px'>Interrogation en cours ...</span>";

//console.log('act 1 : '+id_veh);
let request=new XMLHttpRequest();
request.open("GET",'./get_autobiz1.php?id='+id_veh);
request.onload = function(e) 
	{
	var ret = request.response; // n'est pas responseText
	var value=ret.split('|');
	document.getElementById("prixpart").innerHTML="<span style='font-family:arial;font-size:10px'><b>Prix du march&eacute;</b> : "+value[0]+" &euro;<br><b> Rotation de vente :</b> "+value[1]+" jours</span>";

	}
request.send();
}	

		
function getInfoVehicule(immat) {
	let request1=new XMLHttpRequest();
	var val1=immat.split('-');
	if ( immat.length == 7 ||  ( immat.length == 9 && val1[2] != '' ) ||  immat.length == 8  )
		{
		console.log('IMMAT : ' +immat);
		request1.open("GET",'./get_autobiz.php?immat='+immat);
		request1.onload = function(e) 
			{
			var ret1 = request1.response; // n'est pas responseText
		//	console.log(ret1);
			var val1=ret1.split('||');
			document.getElementById("marque_div").innerHTML =val1[1];
			document.getElementById("modele_div").innerHTML =val1[2];
			document.getElementById("couleur_div").innerHTML =val1[4];
			document.getElementById("motorisation_div").innerHTML =val1[3];
			document.getElementById("marque").value =val1[1];
			document.getElementById("modele").value =val1[2];
			document.getElementById("couleur").value =val1[4];
			document.getElementById("datemes").value =val1[5];
			document.getElementById("motorisation").value =val1[3];
			//console.log('VAL '+val1.length);
			
			var val2=val1[6];
			var val3=val2.split('|');
			//console.log(val2);
			document.form.finition.options.length=0;
			//document.form.finition.options[0]=new Option('-','-', true, false);
			for (i = 0; i < val3.length-1; i++)
				{
				val4=val3[i];
				var val5=val4.split('=');
				
				var val7=val5[0]+'=='+val5[1];
				//console.log(i+' : '+val7);
				document.form.finition.options[i+1]=new Option(val5[1],val7, false, false);
				}
			//document.getElementById("vehinfo").innerHTML ="<br><span style='font-family:arial;font-size:10px'><b> Marque :</b> "+value[0] +'<br><b> Mod&egrave;le :</b> '+value[1]+'<br><b> Finition PVO:</b> '+value[2]+'<br><b> Immatriculation :</b> '+value[3]+'<br><b> KM :</b> '+value[4]+' <br><b> Ann&eacute;e :</b> '+value[5]+'<br><b>Mois:</b> '+value[6]+'<br><b> Frais :</b> '+value[7]+'<br><b> Frais Estim&eacute;s :</b> '+value[8]+'<br><b> Prix Vente :</b> '+value[9]+'<br><b> Prix Marchand :</b> '+value[10]+"<br><b>Reference Autobiz :</b><br><br> <select name='modeleid' style='font-family:georgia,garamond,serif;font-size:10px' onChange='getPrice(this.value)'>"+opt+"</select></span>";
	//		console.log('OPT : '+opt);
			}
		request1.send();
		}	
	else	
		{
		document.getElementById("marque").innerHTML ="";
		document.getElementById("modele").innerHTML ="";
		document.getElementById("couleur").innerHTML ="";
		document.getElementById("motorisation").innerHTML ="";
		console.log('IMMAT TROP COURTE');
		}
}

//document.getElementById("fraiscarrosserie").value=0;
//document.getElementById("fraisjante").value=0;

function plus (id,prix)
{
console.log(id +' : '+prix);	
document.getElementById(id).value =Number(document.getElementById(id).value)+Number(1);
// limitation du nombre en fonction du type
if ( id == 'fraisjante' && document.getElementById(id).value > 4 ) { document.getElementById(id).value =Number(4); }
//if ( id == 'fraisjantebt' && document.getElementById(id).value > 4 ) { document.getElementById(id).value =Number(4); }
if ( id == 'fraisembrayage' && document.getElementById(id).value > 1 ) { document.getElementById(id).value =Number(1); }
if ( id == 'fraisdistribution' && document.getElementById(id).value > 1 ) { document.getElementById(id).value =Number(1); }
if ( id == 'fraisrevision' && document.getElementById(id).value > 1 ) { document.getElementById(id).value =Number(1); }
if ( id == 'fraispneu' && document.getElementById(id).value > 4 ) { document.getElementById(id).value =Number(4); }
if ( id == 'fraisparebrise' && document.getElementById(id).value > 1 ) { document.getElementById(id).value =Number(1); }

document.getElementById(id+"t").value =Number(document.getElementById(id).value)*Number(prix);
document.getElementById('frais').value=Number(document.getElementById('fraiscarrosseriet').value)+Number(document.getElementById('fraisjantet').value)+Number(document.getElementById('fraisrevisiont').value)+Number(document.getElementById('fraisembrayaget').value)+Number(document.getElementById('fraisdistributiont').value)+Number(document.getElementById('fraispneut').value)+Number(document.getElementById('fraisparebriset').value)+Number(document.getElementById('fraisnettoyaget').value)+Number(document.getElementById('fraisdivers').value);

}
function moins (id,prix)
{
console.log(document.getElementById(id).value);

document.getElementById(id).value =Number(document.getElementById(id).value)-Number(1);
if (document.getElementById(id).value < 0 ) { document.getElementById(id).value=0;}
document.getElementById(id+"t").value =Number(document.getElementById(id).value)*Number(prix);
document.getElementById('frais').value=Number(document.getElementById('fraiscarrosseriet').value)+Number(document.getElementById('fraisjantet').value)+Number(document.getElementById('fraisrevisiont').value)+Number(document.getElementById('fraisembrayaget').value)+Number(document.getElementById('fraisdistributiont').value)+Number(document.getElementById('fraispneut').value)+Number(document.getElementById('fraisparebriset').value)+Number(document.getElementById('fraisnettoyaget').value)+Number(document.getElementById('fraisdivers').value);

}

function updatefrais ()
{
console.log(document.getElementById('fraisdivers').value);
document.getElementById('frais').value=Number(document.getElementById('fraiscarrosseriet').value)+Number(document.getElementById('fraisjantet').value)+Number(document.getElementById('fraisrevisiont').value)+Number(document.getElementById('fraisembrayaget').value)+Number(document.getElementById('fraisdistributiont').value)+Number(document.getElementById('fraispneut').value)+Number(document.getElementById('fraisparebriset').value)+Number(document.getElementById('fraisnettoyaget').value)+Number(document.getElementById('fraisdivers').value);

}


function transferComplete(evt) {
	active_button--;
	document.getElementById(fic_type).innerHTML = "";
	//document.getElementById("avant").innerHTML = "";
	alert(fic_type);
	if ( active_button == 0 )
		{
		document.getElementById("Soumettre").disabled = false;
		}	
    //alert("Le transfert est terminé.");
}

function errorTransfert(evt) {
	alert ('Erreur lors du transfert de la photo, Merci de reprendre la photo');
}

function updateProgress (evt) 
{
    var percentComplete = evt.loaded / evt.total;
}



function check_val()
{
//console.log('VAL :' + document.getElementById("finition").value+':');

var daylok='';
if ( document.getElementById("datelivraison").value != '' ) 
	{ 
	var today = new Date();
	var mois=today.getMonth();
	mois++;
	var jour=today.getDate();
	if ( jour < 10 ) { jour='0'+jour;}
	if ( mois < 10 ) { mois='0'+mois;}
	var day=today.getFullYear()+''+mois+''+jour;
	var dayl=document.getElementById("datelivraison").value;
	var dayl1=dayl.split('-');
	var dayl2=dayl1[0]+dayl1[1]+dayl1[2];
	console.log(day+' : ' +dayl2);
	if ( dayl2 >= day )	
		{ daylok='ok';	console.log('DAY SUP : '+daylok);}			
	
	}	

if ( document.getElementById("marque").value != '-' )
		{
		if ( document.getElementById("modele").value != '-' )
			{
			if ( document.getElementById("motorisation").value != '-' )
				{
						if ( document.getElementById("couleur").value != '-'  )
							{
							if ( document.getElementById("immatriculation").value != ''  )
								{
								if ( document.getElementById("kmactuel").value != ''  )
									{
									if ( document.getElementById("kmlivraison").value != ''  )
										{
										if ( daylok =='ok' )
											{
											if ( document.getElementById("datemes").value != ''  )
												{
												if ( document.getElementById("frais").value != ''  )
													{
											
													active_variable=0;
													console.log("CHECK :"+ active_variable +' : '+active_button);
													if ( active_button <= 0 && active_variable ==0)
														{
														document.getElementById("Soumettre").disabled = false;
														}
													}	
												
												}
											}
										}
									}	
								}	
							}	
							
						
				}	
			}	
		}
}	


function updMemMarch(data) {
	var xhr = new XMLHttpRequest();
	console.log('DATA :'+data);
	xhr.open('GET', 'https://app.cobredia.bzh:11443/dcapvo/webapp/updatecom_marchand.php?data='+data);
	xhr.send();
	
}

function sendData(data, url,fichier,cotid,fic_type) {
	// gestion de l'envoi des photos a chaque photos au lieu d'atetndre le submit
   	var xhr = new XMLHttpRequest();
	var data = "cotid="+cotid+"&fichier="+fichier+"&data="+data;
	//xhr.addEventListener("progress", updateProgress, false);
	xhr.onload = function(evt)
			{
			document.getElementById(fic_type).innerHTML = "<FONT SIZE='2'>Chargement termin&eacute;</FONT>";
			if ( fic_type == 'avant' || fic_type == 'arriere' || fic_type == 'gauche' ||fic_type == 'droite' ||fic_type == 'int1' ||fic_type == 'int2' || fic_type == 'cg' || fic_type == 'rep' )
					{	
					active_button--; 
					console.log(active_button);			
					check_val();		
					}
			if ( active_button <= 0 && active_variable ==0 )
				{
				document.getElementById("Soumettre").disabled = false;
				}		
			
			} ;
	xhr.addEventListener("error",function(evt)	{	//alert ('Erreur lors du transfert de la photo, Merci de reprendre la photo');
				document.getElementById(fic_type).innerHTML = "<FONT SIZE='2'>Chargement en erreur : nouvelle tentative</FONT>";
				pausecomp(5000);
				//alert('fini');
				var xhr1 = new XMLHttpRequest();
				xhr1.onload = function(evt){
				document.getElementById(fic_type).innerHTML = "<FONT SIZE='2'>Chargement termin&eacute;</FONT>";
				if ( fic_type == 'avant' || fic_type == 'arriere' || fic_type == 'gauche' ||fic_type == 'droite' ||fic_type == 'int1' ||fic_type == 'int2' || fic_type == 'cg' || fic_type == 'rep' )
				{	active_button--; console.log(	active_button);		check_val();	}
			
				if ( active_button <= 0 && active_variable ==0)
						{
						document.getElementById("Soumettre").disabled = false;
						}		
			
					} ;
				xhr1.open('POST', url);
				xhr1.send(data);
			//	document.getElementById(fic_type).innerHTML = data;
				
			} ,false);
	
	xhr.open('POST', url);
	xhr.send(data);
	document.getElementById(fic_type).innerHTML = "<FONT SIZE='2'>Chargement en cours...</FONT> ";
	//console.log(data);
	
}

function pausecomp(millis)
{
    var date = new Date();
    var curDate = null;
    do { curDate = new Date(); }
    while(curDate-date < millis);
}

// gestion dela taille d'afficahge
setCookie('CAPVO_WIDTH', screen.width, 3600);
setCookie('CAPVO_HEIGHT', screen.height, 3600);

$(document).ready(function()
	{
    var imagesPreview = function(input, placeToInsertImagePreview,fic_type,order) 
		{
			if (input.files) 
			{
				var filesAmount = input.files.length;
				var data ="";
				//var content ="";
				for (i = 0; i < filesAmount; i++) 
			
					{
					var reader = new FileReader();
					var nom ="";
					var cotid="";
					nom=input.files[i].name;
					nom=order+'_'+fic_type+'_'+nom;
					cotid=document.getElementById('cotid').value
					//console.log(input.files[i].name);
					reader.onload = function(event)
							{
							data = reader.result;
							sendData(data, './save-img.php',nom,cotid,fic_type);
							$($.parseHTML('<img>')).attr('src', event.target.result).appendTo(placeToInsertImagePreview);
							}
					reader.readAsDataURL(input.files[i]); 
					}
			}
		}; 

	
	//fonction de gestion des boutons divers
	function show_next(event){
		
		//recupere id
		var input_id = event.target.id;
		
		//recupere dans l'id la partie sans photo_
		var next_id = input_id.replace("photo_",".hidden-by-");
		
		//Class hidden remove
		$(next_id).removeClass("hidden");
		
		console.log(next_id);
		
	};
	
	//fonction d'affichage des images et camoufles celle déjà affichée
	function update_preview(event) {
		var input_id = event.target.id;
		var preview_div = $("tr.block_" + input_id + " div.Previsualisation");
		var message_div = $("tr.block_" + input_id + " .message-zone");
		var bouton_info = $("tr.block_" + input_id + " button.BoutonPhoto");

		// Masque toutes les images:
		$("tr.block-image-field div.Previsualisation").css("display", "none");
		
		//Texte bouton = "Affiche Photo"
		$("tr.block-image-field button.BoutonPhoto").html("+");
		
		//Vide la div
		preview_div.empty();
		
		//affiche bouton review
		bouton_info.removeClass("hidden");
		
		//affiche "Fermer la photot sur le bouton"
		bouton_info.html("-");

		//affiche la div pour l"image a chargé		
		preview_div.css("display", "block");
		
		//affichel"image
		imagesPreview(event.target, preview_div, message_div.attr("id"), preview_div);
		
		// if(preview_div.findbyselector('img')){
		if(preview_div.hasClass("NotLoaded")){
			//Retire la class NotLoaded et rajoute la class Loaded
			preview_div.addClass("Loaded").removeClass("NotLoaded");		
		};
		
		//cache le chargement terminée au bout de 3s
		window.setTimeout(function(){
			message_div.css("display","none");
        }, 1250);
			
	};	

	//au chargement d'une image execute la fonction update_preview
    $('tr.block-image-field input.image-input').change(update_preview);
	$('tr.block-image-field input.show-next').change(show_next);

	//fonction sur clic bouton pour revoir la photo chargée
	function replay_photo(event){
		
		//id du bouton
		var button_id = (event.target.id).split("-")[0];
		
		//id de la div a afficher
		var div_image = $("tr.block_photo_" + button_id + " div.Previsualisation");
		
		//vérifie si la div est affichée
		if((div_image.css("display") == "none") && (div_image.hasClass("Loaded"))){
					
			//affiche l"image
			div_image.css("display", "block");
			
			//affiche "-" sur le bouton
			$("tr.block_photo_" + button_id + " button.BoutonPhoto").html("-");
		
		} else {
		
			//affiche l"image
			div_image.css("display", "none");
			
			//affiche "+" + button_id sur le bouton
			$("tr.block_photo_" + button_id + " button.BoutonPhoto").html("+");
		};
	};
	
	//clic d'un bouton execute la fonction replay_photo
	$('tr.block-image-field button.BoutonPhoto').click(replay_photo);

})

let socket = new WebSocket("wss://app.cobredia.bzh:11443/wss/");

socket.onopen = function(e) {
  // socket.send("");
};

function checkvalue(input , Write) {
  var inputValue = document.getElementById(input).value;
  if(inputValue !="" && inputValue !=null) { 
    document.getElementById(Write).innerHTML = inputValue;
  } else { 
    document.getElementById(Write).innerHTML = "Value is empty";
  }
}

function Reload ()
{
document.location.reload();
}

// update en fonction de reception message Websocket
	// MAJ d'un autre marcnahd 
	// MAJ pour cloture 19H
	
var post = '<?php echo $_POST['Soumettre'];?>';
socket.onmessage = function(event) {
  var ret=(`${event.data}`); 
  console.log('RET :  '+ret+ ' : ' +post); 
  var value=ret.split('|');
  if (( value[0] == 'updateMarchand' ) && ( getCookie('USER_PROFILE')  == 'MARCHAND' ))
	{
	if ( post == '' )
		{
		let request=new XMLHttpRequest();
		request.open("GET",'./get_updatecvt.php');
		request.onload = function(e) 
			{
			var ret = request.response; // n'est pas responseText
			var value=ret.split('|');
			document.getElementById("temps_cvt").innerHTML=value[0];
			document.getElementById("statut_cvt").innerHTML=value[1];
			//document.getElementById("statut_capvo").innerHTML=value[2];
			}
		request.send();
		//document.getElementById('temps_cvt').innerHTML = "finiiit";	
		//document.location.reload();
		}
	}
  if ( (value[2] == 'all')  && ( getCookie('USER_PROFILE')  == 'MARCHAND' ) )
	{
		document.location.reload();
		
	}	
};

socket.onclose = function(event) {
  if (event.wasClean) {
    console.log(`[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`);
  } else {
    console.log('[close] Connection died');
	setTimeout(socket.onopen, 10000)
  }
}; 

socket.onerror = function(error) {
  console.log(`[error] ${error.message}`);
};

function clearMsg()
{
document.getElementById("msg").innerHTML = "";
return true;
}

function sendSocket ()
{
var input = document.getElementById("input");
socket.send(input.value);
}

function sendSock(act,val)
{
pausecomp(800);
console.log(act+'|'+val);
socket.send(act+'|'+val);
}

function onSubmit() {
 var input = document.getElementById("input");
 socket.send(input.value); 
 input.value = "";
 input.focus();  
}

function redir(name,val)
{

if ( val != '-' && val != '' )
	{
	document.location.href='./index.php?f=lst&'+name+'='+val;
	}

}

function redirm(name)
{
if ( name == 'immatriculation')
	{
	var elements = document.getElementById("vehicule_id").options;
    for(var i = 0; i < elements.length; i++){
		elements[i].selected = false;
		}
	}
//console.log(val);
//if ( name == 'vehicule_id')
	//{
	//var elements = document.getElementById("immatriculation").options;
   //for(var i = 0; i < elements.length; i++){
	//	elements[i].selected = false;
	//	}
//	}
document.forms["form_marchand"].submit();
/*if ( val != '-' && val != '' )
	{
	document.location.href='./index.php?f=lstcvt&'+name+'='+val;
	} */
}

function updStatut(val,type,id)
{
if  ( val != '-' && val != '')
	{
	document.location.href='./index.php?f='+type+'&val='+val+'&vehicule_id='+id;
	sendSock('update',id);
	}
}

function deconnect ()
{
	setCookie('USER_PROFILE', '', 2);
	setCookie('CAPVO_ID', '', 2);
	setCookie('COTEUR_ID', '', 2);
	setCookie('COTEUR_PROFILE', '', 2);
	setCookie('USER_PROFILE', '', 2);
	setCookie('ROWID', '', 2);
	setCookie('VEHICULE_ID', '', 2);
	setCookie('CAPVO_TOKEN', '', 2);
	setCookie('CAPVO_UPDMAJ', '', 2)
	document.location.href='./index.php';
	
}

function setCookie(c_name,value,exdays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var c_value = escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
   document.cookie=c_name + "=" + c_value;
 }

</script>
<body background-color='#eeeeee'>

<?php 

topHtml($pdo,$token);

if ( $_POST['connecter'] == 'Connecter' && $_COOKIE['USER_PROFILE'] =='' )
	{
	identForm($pdo,'Mot de passe incorrect');
	}
else if ( $_POST['Soumettre'] == 'Soumettre la demande' )
	{
	loadCotation($pdo);
	//echo "<script>setTimeout(document.location.href='./index.php', 20000)</script>";

	}
else if ( ( $_GET['f'] == 'lst' ||  $_GET['f'] == 'updst' || $_GET['f'] == 'updkm' || $_GET['f'] == 'upddl' ) && $_COOKIE['USER_PROFILE'] == 'VENDEUR')
	{

	updCotation($pdo,$_GET['vehicule_id'], $_GET['f'],$_GET['val']); 
	lstCotation($pdo,$_GET['vehicule_id'],$_GET['immatriculation']); 
	}
	
else if ( $_GET['f'] == 'lstcvt'  )
	{
	lstCvt($pdo,$_POST['vehicule_id'],$_POST['immatriculation']); 
	}
else if ($_GET['f'] == 'msg' )
	{
	
	}
else if ($_GET['f'] == 'new' && $_COOKIE['USER_PROFILE'] == 'VENDEUR' )
	{
	newCotation($pdo);

	}
else if ($_GET['f'] == 'mdp' )
	{
	mdpForm($pdo,$msg);

	}
else if (  $_COOKIE['USER_PROFILE'] =='' )
	{
	identForm($pdo,$msg);
	}
else
	{

	//verifIdent($pdo);
	}

function topHtml($pdo,$token)
{
require("../config.php");
//phpinfo();
//print_r ($_POST);
echo "<table width='344' border='0' cellspacing='0'>

<tr><td colspan='2' height='70' align='center' bgcolor='#DDDDDD'><img src='/capvo/img/logo.png' width='80'></img>&nbsp;&nbsp;<img src='/capvo/img/logo-1024t.png' width='222'></img></td></tr>
<tr><td colspan='2' height='15' bgcolor='#DDDDDD'><div id='msg'>".$msg."</div></td></tr>";
//echo "COOK : ".$_COOKIE['CAPVO_TOKEN'];
if ( $_COOKIE['CAPVO_TOKEN'] !='')
	{
	echo "<tr><td colspan='2' height='15' bgcolor='#DDDDDD'>&nbsp;&nbsp;<a href='#' onClick='deconnect();'>Se d&eacute;connecter</a></td></tr>";
	
	}
else
	{
	echo "<tr><td colspan='2' height='15' bgcolor='#DDDDDD'>&nbsp;&nbsp;</td></tr>";
	identForm($pdo,$msg);
	exit();
	}

		

if ( $_COOKIE['CAPVO_WEBAPP_VERSION'] == '' && $_COOKIE['DEVICE'] == 'android' ) { echo "<tr><td colspan='2' height='20' bgcolor='#DDDDDD'><a href='./app-capvo.apk'>Merci de mettre &agrave; jour votre application en cliquant sur ce lien</a><br><br>-- Correctif : Compression des photos prisent par l'application<br>
	<br>T&eacute;l&eacute;charger le fichier puis aller dans le dossier t&eacute;l&eacute;chargement et cliquer sur le fichier app-capvo.apk<br>En vous remerciant<br>L'&eacute;quipe CAPVO<br><br></td></tr>"; }
	else { echo "<tr><td bgcolor='#DDDDDD'></td><td height='20' bgcolor='#DDDDDD' align='right'><a href='./app-capvo.apk'>Application</a>&nbsp;&nbsp;&nbsp;</td></tr>"; }

if ( $_COOKIE['USER_PROFILE'] == 'VENDEUR' )
	{
		
	$sql="select vendeur_nom, vendeur_prenom from vendeurs where token_id ='". $_COOKIE['CAPVO_TOKEN']."';";

	file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
	$stm=$pdo->query($sql) ; 
	$row=$stm->fetch(); 
	if ( $row[0] == '' )
		{
		identForm($pdo,$msg);
		exit();
		}
	echo "<tr><td height='5' bgcolor='#DDDDDD' align='right' colspan='2'></td></tr>";
	echo "<tr><td  height='20' bgcolor='#DDDDDD' align='right'></td><td bgcolor='#DDDDDD'>".$row[1]." ".$row[0]."</td></tr>";
		
	$color1="#DDDDDD";$color2="#DDDDDD";
	if ( $_GET['f'] == 'lst' ||  $_GET['f'] == 'updst' || $_GET['f'] == 'updkm' || $_GET['f'] == 'upddl' ) { $color1="#DDDDDD";$color2="#EEEEEE";}
	if ( $_GET['f'] == 'new'  ) { $color1="#EEEEEE";$color2="#DDDDDD";}
	echo "<tr><td height='40' width='172' align='center' bgcolor='".$color1."'><a href='#' onclick=\"document.location.href='./index.php?f=new';\">Nouvelle Cotation</a></td><td height='40' width='172' align='center' bgcolor='".$color2."'><a href='#' onclick=\"document.location.href='./index.php?f=lst';\">Liste des Cotations</a></td></tr>";
	echo "<tr><td colspan='2'>";
	}
else if ( $_COOKIE['USER_PROFILE'] == 'MARCHAND' )
	{
	
	$sql="select vendeur_nom from  marchand_vendeurs where token_id ='". $_COOKIE['CAPVO_TOKEN']."';";
	file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
	$stm=$pdo->query($sql) ; 
	$row=$stm->fetch(); 
	if ( $row[0] == '' )
		{
		identForm($pdo,$msg);
		exit();
		}
	echo "<tr><td height='5' bgcolor='#DDDDDD' align='right' colspan='2'></td></tr>";
	
	echo "<tr><td height='20' bgcolor='#DDDDDD' align='right'>Soci&eacute;t&eacute; :</td><td bgcolor='#DDDDDD'>".$row[0]."</td></tr>";
	$color1="#DDDDDD";$color2="#DDDDDD";
	if ( $_POST['f'] == 'lstcvt' ) { $color1="#DDDDDD";$color2="#EEEEEE";}
	echo "<tr><td height='40' width='172' align='center' bgcolor='".$color1."'><a href='#' id='lstcvt' onclick=\"document.location.href='./index.php?f=lstcvt';\" >Liste des couvertures</a></td><td height='40' width='172' align='center' bgcolor='".$color2."'></td></tr>";
	echo "<tr><td colspan='2'>";
	}
	
}


function lstCvt($pdo,$vehicule_id,$immatriculation)
{
//	echo 'INF: '.$vehicule_id.$immatriculation;
require("../config.php");
$check='';
$check1='';
$filter=' ';
if ( $_POST['retenu'] == 'on' ) 
		{
		$check='checked'; 
		}
	
//MODIF TJO
if ( $_POST['encours'] == 'on' ) 
		{
		$check1='checked'; 
		}
echo "<form name='form_marchand' id='form_marchand' method=\"post\"><table border='0' cellspacing='0' cellpadding='0'>
	<tr><td height='25' width='345'  bgcolor='#EEEEEE' colspan='2'>&nbsp;V&eacute;hicules retenus : <input type='checkbox' name='retenu'  onClick=\"redirm('vehicule_id')\" ".$check." ></input>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;En cours : <input type='checkbox' name='encours'  onClick=\"redirm('vehicule_id')\" ".$check1." ></input></td></tr>
	<tr><td height='25' width='345' colspan='2' bgcolor='#EEEEEE'>&nbsp;N&deg; : <select id='vehicule_id' name='vehicule_id' onchange=\"redirm('vehicule_id')\" width='300'>".lstm($pdo,'vehicule_id',$vehicule_id,'','','',$check,$check1)."</select></td></tr>
	<tr><td height='25' width='150' colspan='2' bgcolor='#EEEEEE'>&nbsp;Immatriculation : <input id='immatriculation' autocomplete='off' type='text' name='immatriculation' placeholder='Saisir Immatriculation'>&nbsp;&nbsp;<input type='submit' name='Ok' value ='Ok'></input></td></tr>
		</table>";
//MODIF TJO	
		
//	<tr><td height='25' width='150'  bgcolor='#EEEEEE'>&nbspImmatriculation : </td><td width='194' bgcolor='#EEEEEE'><select id='immatriculation' name='immatriculation' onchange=\"redirm('immatriculation')\">".lstm($pdo,'immatriculation',$immatriculation,'','','',$check)."</select></td></tr>
?>
<script>
let request=new XMLHttpRequest();
request.open("GET",'./get_lstimmat.php');
request.onload = function(e) 
	{
	var ret = request.response; // n'est pas responseText
	var value=ret.split('|'); 
	autocomplete(document.getElementById('immatriculation'), value);
	//console.log(ret);
	//console.log(ret);
	}
request.send();
//autocomplete(document.getElementById('immatriculation'), countries);
</script>
<?php 
$sql="";
$sql1="";
$sql2="";
$sql3="";
$sql4="";
$sql5="";
//echo $immatriculation;
				
if ( $immatriculation != '-' && $immatriculation != '' ) 
	{
	$id=explode(":",$immatriculation);
	//$sql="select v.vehicule_id,immatriculation, marque_nom, modele_nom, finition_nom, v.statut_id,mnt_bca,mnt_capvo,date_format(date_couverture,'%d/%m/%Y %H:%i:%s'),km_livraison,date_format(date_livraison,'%d/%m/%Y'),statut_libelle,commentaire_desc, DATEDIFF(now(),date_couverture),date_format(newdate_livraison_final,'%d/%m/%Y'),mnt_frais,origine,date_format(now(),'%H%i'),date_format(adddate(date_couverture, INTERVAL 1 DAY ),'%Y-%m-%d'), now(),remarque_vehicule,date_format(date_miseenservice,'%d/%m/%Y'), co.coteur_nom,c.commentaire_couv from vehicule_couverts v, statut_cotation s, commentaires c ,marchand_vendeurs m,coteurs co where v.coteur_id=co.coteur_id and  m.marchand_id=c.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and v.vehicule_id=".$id[1]." and s.statut_id=v.statut_id and v.vehicule_id=c.vehicule_id and c.commentaire_type = 'marchand' order by c.commentaire_type asc;"; 
	
	//$sql="select v.vehicule_id,immatriculation, marque_nom, modele_nom, finition_nom, v.statut_id,mnt_bca,mnt_capvo,date_format(date_couverture,'%d/%m/%Y %H:%i:%s'),km_livraison,date_format(date_livraison,'%d/%m/%Y'),statut_libelle,commentaire_desc, DATEDIFF(now(),date_couverture),date_format(newdate_livraison_final,'%d/%m/%Y'), mnt_frais,origine,date_format(now(),'%H%i'), date_format(adddate(date_couverture, INTERVAL 1 DAY ),'%Y-%m-%d'), now(),remarque_vehicule,date_format(date_miseenservice,'%d/%m/%Y'), co.coteur_nom from vehicule_couverts v, statut_cotation s, commentaires c ,marchand_vendeurs m, coteurs co where  v.coteur_id=co.coteur_id and  m.marchand_id=c.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and v.immatriculation='".$immatriculation."' and s.statut_id=v.statut_id and c.commentaire_type='marchand' order by date_couverture desc; "; 
	//$stm=$pdo->query($sql);
	//$row = $stm->fetch();
	$vehicule_id=$id[1];
	}
	
if ( $vehicule_id != '-' && $vehicule_id != '' )
	{ 
	$sql="select v.vehicule_id,immatriculation, marque_nom, modele_nom, finition_nom, v.statut_id,mnt_bca,mnt_capvo,date_format(date_couverture,'%d/%m/%Y %H:%i:%s'),km_livraison,date_format(date_livraison,'%d/%m/%Y'),statut_libelle,commentaire_desc, DATEDIFF(now(),date_couverture),date_format(newdate_livraison_final,'%d/%m/%Y'),mnt_frais,origine,date_format(now(),'%H%i'),date_format(adddate(date_couverture, INTERVAL 1 DAY ),'%Y-%m-%d'), now(),remarque_vehicule,date_format(date_miseenservice,'%d/%m/%Y'), co.coteur_nom,c.commentaire_couv ,date_format(adddate(date_couverture,INTERVAL 1 DAY),'%w'), date_format(adddate(date_couverture, INTERVAL 2 DAY ),'%Y-%m-%d') from vehicule_couverts v, statut_cotation s, commentaires c ,marchand_vendeurs m,coteurs co where v.coteur_id=co.coteur_id and  m.marchand_id=c.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and v.vehicule_id=".$vehicule_id." and s.statut_id=v.statut_id and v.vehicule_id=c.vehicule_id and c.commentaire_type = 'marchand' order by c.commentaire_type asc;"; 
	$sql1="select commentaire_desc from vehicule_couverts v,commentaires c where v.vehicule_id=c.vehicule_id and v.vehicule_id=".$vehicule_id." and c.commentaire_type='frais'; "; 
	$sql6="select commentaire_desc from vehicule_couverts v,commentaires c where v.vehicule_id=c.vehicule_id and v.vehicule_id=".$vehicule_id." and c.commentaire_type='coteurmarchand'; "; 
	
	$sql2="select count(*) from marchand_couvertures c,marchand_vendeurs m  where m.marchand_id=c.marchand_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and  m.marchandvendeur_id ='".$_COOKIE['CAPVO_ID']."' and  c.vehicule_id=".$vehicule_id."; "; 
	$sql3="select round(max(mnt_offre)), date_offre from marchand_couvertures c,marchand_vendeurs m  where m.marchand_id=c.marchand_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and c.vehicule_id=".$vehicule_id."; "; 
	$sql4="select acheteur_nom from vehicule_couverts v where v.vehicule_id=".$vehicule_id.";"; 
	$sql5="select marchand_nom from marchand_vendeurs v, marchands m where v.marchand_id=m.marchand_id and v.marchandvendeur_id=".$_COOKIE['CAPVO_ID'].";"; 
	}

file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
file_put_contents($logfilew,$sql1."\r\n",FILE_APPEND);
file_put_contents($logfilew,$sql2."\r\n",FILE_APPEND);
file_put_contents($logfilew,$sql3."\r\n",FILE_APPEND);
file_put_contents($logfilew,$sql4."\r\n",FILE_APPEND);
file_put_contents($logfilew,$sql5."\r\n",FILE_APPEND);

$stm=$pdo->query($sql);
$row = $stm->fetch();

$stm1=$pdo->query($sql1);
$row1 = $stm1->fetch();

$stm2=$pdo->query($sql2);
$row2 =$stm2->fetch();

$stm3=$pdo->query($sql3);
$row3 = $stm3->fetch();

$stm4=$pdo->query($sql4);
$row4 = $stm4->fetch();

$stm5=$pdo->query($sql5);
$row5 = $stm5->fetch();

$stm6=$pdo->query($sql6);
$row6 = $stm6->fetch();

$row1[0]=str_replace("Frais","<br>Frais",$row1[0]);

//$start_date = new DateTime($row[18].' 19:00:00');

	
$com=explode("\n",$row[20]);
$com1=explode("Commentaire vehicule :",$com[10]);

	
echo "<tr><td colspan='3'><table border='0' cellspacing='0' cellpadding='4' bgcolor='#EEEEEE'>
	<tr><td height='25' width='160'><b>N&deg; Dossier : </b></td><td width='184'>".$row[0]."</td></tr>
	<tr><td height='25' margin-left:15'>Immatriculation : </td><td>".$row[1]."</td></tr>
	<tr><td height='25' >Marque : </td><td>".$row[2]."</td></tr>
	<tr><td height='25' >Modele : </td><td>".$row[3]."</td></tr>
	<tr><td height='80' >Finition : </td><td>".$row[4]."</td></tr>
	<tr><td height='25' >Date de MEC : </td><td>".$row[21]."</td></tr>
	<tr><td height='25' >Date du dossier : </td><td>".$row[8]."</td></tr>";
	

	
echo "<tr><td height='25' >Km liv estim&eacute;e : </td><td>".$row[9]."</td></tr>
	<tr><td height='25' >Date liv estim&eacute;e : </td><td>".$row[10]."</td></tr>
	<tr><td height='25' >Nouv date livraison : </td><td>".$row[14]."</td></tr>
	<tr><td height='25' >Origine du v&eacute;hicule : </td><td>".$row[16]."</td></tr>
	<tr bgcolor='#FFFFFF' height='2'><td colspan=\"2\"></td></tr>
	<tr><td height='25' >Statut CapVo :  </td><td><div id='statut_capvo'>".$row[11]."</div></td></tr>
	<tr><td height='25' >Coteur :  </td><td>".$row[22]."</td></tr>
	<tr><td height='25' >Photos : </td><td><a href='#' onclick=\"showPhotos(0);\" style='color:white;background-color:#FF7F00;text-decoration:none;font-size:14px;padding:4px'>Visualiser les photos</a></td></tr>";
	

echo "<tr bgcolor='#FFFFFF' height='2'><td colspan=\"2\"></td></tr>
	<tr bgcolor='#90C8E1'><td height='25' colspan=\"2\"><b>Frais Estim&eacute;s </b></td></tr>
	<tr bgcolor='#90C8E1'><td>Frais coteur :</td><td>".$row[15]." &euro;</td></tr>
	<tr bgcolor='#90C8E1'><td height='60' valign='top'> Commentaire <br>coteur :</td><td>".$row6[0]."</td></tr>
	<tr bgcolor='#90C8E1'><td height='15' ></td><td></td></tr>
    <tr bgcolor='#90C8E1'><td height='60' valign='top'>Commentaire vendeur :</td><td>".$com1[1]."<br>".$com[11]."<br>".$com[12]."</td></tr>
	<tr bgcolor='#90C8E1'><td height='25' colspan=\"2\">".$row1[0]."</td></tr>
	<tr bgcolor='#FFFFFF' height='2'><td colspan=\"2\"></td></tr>";

//echo "D TOMO : ".$row[24];
if ( $row[24] == '0' )
	{	
	$start_date = new DateTime($row[25].' 19:00:00');
	}
else
	{	
	$start_date = new DateTime($row[18].' 19:00:00');
	}

//$start_date = new DateTime($row[18].' 19:00:00');

	
$since_start = $start_date->diff(new DateTime($row[19]));
$minutes = $since_start->days * 24 * 60;
$minutes += $since_start->h * 60;
$minutes += $since_start->i;



$jour=$since_start->days;
$heure=$since_start->h;
$min=$since_start->i;
$statut_couv='open';	
$time1=date('Hi');


	
$ret_marchand="Pas suffisant";                
if ( $row4[0] == 	$row5[0] )
	{
	$ret_marchand='Retenue';
	}

//print_r($com);


	
$htmltpsrestant="";

if ( $row[13] <= $nbjcouverture &&  $row[17] !=0 )
	{
	//	echo "<tr><td height='25' >Temps restant  :  </td><td>".$minutes ." minutes </td></tr>";
		$tpsrestant=($nbcouverture-$row[13]);
		if ( $tpsrestant== '1' )
			{
			if ( $time1 < 1900 )
				{
				$htmltpsrestant= "<tr bgcolor='#B0F2B6'><td height='25' >Temps restant : </td><td><b><div id='temps_cvt'>".$jour. " Jrs ". $heure. " Hrs " . $min." Mins</div></b></td></tr>";
				}
			else
				{
				$htmltpsrestant= "<tr bgcolor='#B0F2B6'><td height='25' >Temps restant :  </td><td><b><div id='temps_cvt'>Termin&eacute;</div></b></td></tr>";
				$statut_couv='close';
				}
			}
		else if ( $tpsrestant >= 2 )
			{
			$htmltpsrestant= "<tr bgcolor='#B0F2B6'><td height='25' >Temps restant  :  </td><td><b><div id='temps_cvt'> ".$jour. " Jrs ". $heure. " Hrs " . $min." Mins</div></b></td></tr>";
			}
		
		else
			{
			$htmltpsrestant= "<tr bgcolor='#B0F2B6'><td height='25' >Temps restant :  </td><td><b><div id='temps_cvt'>Termin&eacute;</div></b></td></tr>";
			$statut_couv='close';
			}	
	}
else	
	{
	$htmltpsrestant= "<tr bgcolor='#B0F2B6'><td height='25' >Temps restant :  </td><td><b><div id='temps_cvt'>Termin&eacute;</div)></b></td></tr>";
	$statut_couv='close';
	}
	
if ( $statut_couv == 'open' && $row2[0] != $nbcouverture)
	{
	echo "<tr bgcolor='#B0F2B6'><td height='35' ><b>Couverture Marchand</b></td><td><a href='#' onclick=\"showCvtGrid(0);\" style='color:white;background-color:#FF7F00;text-decoration:none;font-size:14px;padding:4px'>Ajouter une couverture</a></td></tr>";
	}
else
	{
	echo "<tr bgcolor='#B0F2B6'><td height='35' ><b>Couverture Marchand</b></td><td><a href='#' onclick=\"showCvtGrid(0);\" style='color:white;background-color:#FF7F00;text-decoration:none;font-size:14px;padding:4px'>Visualiser les couvertures</a></td></tr>";
	} 
	
echo "<tr  bgcolor='#B0F2B6'><td height='25'>Mnt couverture : </td><td>".$row3[0]. " &euro;</td></tr>".$htmltpsrestant."
	<tr  bgcolor='#B0F2B6'><td height='25' >Statut :  </td><td><div id='statut_cvt'>".$ret_marchand."</div></td></tr>
	<tr  bgcolor='#B0F2B6'><td height='25' >Nombre :  </td><td>".$row2[0]."</td></tr>";

//modif TJO 

echo "<tr bgcolor='#B0F2B6'><td height='25' colspan=\"2\"><br>Champs d'information pour les marchands :</td></tr>
	<tr bgcolor='#B0F2B6'><td height='25' colspan=\"2\" ><textarea name='marchandmemo' id='marchandinfo' onChange='updMemMarch(this.value);' rows='3' cols='43'>".$row[23]."</textarea> </td></tr>
	</table>";

	
if ( $_COOKIE['CAPVO_WIDTH'] <= 400 )
	{
	echo "<div id=\"photos\"  style=\"background-color:white;display: none;position: fixed;left: 8px;top: 200px;z-index: 99;overflow-y:scroll;height:340px\">
	<table width=\"321px\" height=\"340px\" style=\"border: solid #3D3232 1px; moz-box-shadow: 8px 8px 12px #aaa; box-shadow: 8px 8px 12px #aaa; -webkit-box-shadow: 8px 8px 12px #aaa;\" ><tr><td width='310'></td><td width='16'><a href='#' onclick=\"showPhotos(1);location.reload();\" style='color:black;font-size:20px;color:black;font-size:18px'><b>X</b></a>&nbsp;</td></tr>";
	}
else
	{
	echo "<div id=\"photos\"  style=\"background-color:white;display: none;position: absolute;left: 345px;top: 30px;z-index: 99;overflow-y:scroll;height:680px\">
	<table width=\"900px\" height=\"680px\" style=\"border: solid #3D3232 1px; moz-box-shadow: 8px 8px 12px #aaa; box-shadow: 8px 8px 12px #aaa; -webkit-box-shadow: 8px 8px 12px #aaa;\" ><tr><td width='810'></td><td width='16'></td></tr>";
	}
$files = scandir($dirdoc.$vehicule_id."/");
sort($files, SORT_NUMERIC);
foreach ($files as $entry) 
	{
	if ($entry != '.' && $entry != '..') 
		{
		$type = mime_content_type($dirdoc.$vehicule_id."/".$entry);
		$atype=explode("/",$type );
		$typefile=explode("_",$entry );
		$typefilejpg=explode(".",$entry );
		if ( $typefile[1] == 'avant' || $typefile[1] == 'droite' || $typefile[1] == 'gauche' || $typefile[1] == 'arriere' ||  $typefile[1] == 'int1' || $typefile[1] == 'int2' ||$typefile[1] == 'car1' ||$typefile[1] == 'car2' ||$typefile[1] == 'car3' ||$typefile[1] == 'car4' ||$typefile[1] == 'car5' ||$typefile[1] == 'car6' ||$typefile[1] == 'car7' ||$typefile[1] == 'car8' ||$typefile[1] == 'car9' ||$typefile[1] == 'car10' ||$typefile[1] == 'ct' || $typefile[1] == 'reprise' || $typefile[1] == 'reprisecapvo' )
			{
			if ( $atype[0] == "image" && ($typefilejpg[1] == "jpg" || $typefilejpg[1] == "jpeg" || $typefilejpg[1] == "png" ||  $typefilejpg[1] == "PNG" || $typefilejpg[1] == "JPG" || $typefilejpg[1] == "jfif") && $typefilejpg[2] =='' )
				{
				if ( file_exists ( $dirdoc.$vehicule_id."/".$entry.".jpgl" ) )
					{
					echo "<tr align='center'><td><img src='/".$version."/doc/".$vehicule_id."/".$entry.".jpgl' style=\"max-width:96%;margin:1em\" loading=\"lazy\" ></img></td><td valign='top'><a href='#' onclick=\"showPhotos(1);location.reload();\" style='color:black;font-size:18px;text-decoration:none;color:black;font-size:18px'><b>X</b></a></td></tr>";
					}
				else
					{
					echo "<tr align='center'><td><img src='/".$version."/doc/".$vehicule_id."/".$entry."' style=\"max-width:96%;margin:1em\" loading=\"lazy\" ></img></td></tr>";
					}	
				}
			else if ( $atype[0] == "application" )
				{
				
				if ( $_COOKIE['CAPVO_WIDTH'] <= 400 )
					{
					echo "<tr align='center'><td><embed src=\"/".$version."/doc/".$vehicule_id."/".$entry."\"  style=\"max-width:810px;margin:1em\" type=\"application/pdf\"></td><td valign='top'><a href='#' onclick=\"showPhotos(1);location.reload();\" style='color:black;font-size:18px;text-decoration:none;color:black;font-size:18px'><b>X</b></a></td></tr>";
					}
				else
					{
					echo "<tr align='center'><td><embed src=\"/".$version."/doc/".$vehicule_id."/".$entry."\" width='840px' height='520px' style=\"max-width:810px;margin:1em\" type=\"application/pdf\"></td><td valign='top'><a href='#' onclick=\"showPhotos(1);location.reload();\" style='color:black;font-size:18px;text-decoration:none;color:black;font-size:18px'><b>X</b></a></td></tr>";
					}
				}
			}	
		}
	} 
echo "</table></div>";
if ( $statut_couv == 'open' && $row2[0] != $nbcouverture)
	{
	echo "<div id=\"price_cont\"  style=\"background-color:white;display: none;overflow:hidden;position: absolute;left: 8px;top: 230px;z-index: 99\"><table width=\"321px\" height=\"185px\" style=\"border: solid #3D3232 1px; moz-box-shadow: 8px 8px 12px #aaa; box-shadow: 8px 8px 12px #aaa; -webkit-box-shadow: 8px 8px 12px #aaa;\" ><tr height='25'><td><input type=\"button\" name=\"add\" value=\"Ajouter une couverture\" id='addcvt' style='color:white;background-color:#FF7F00;text-decoration:none;font-size:12px;padding:2px' onclick=\"var id=mygrid_price.uid(); mygrid_price.addRow(id,'');\"></td><td><a href=\"#\" onclick=\"showCvtGrid(1);return false;\" style='position: absolute;right: 5px;top: 6px;color:white;background-color:#FF7F00;text-decoration:none;font-size:12px;padding:2px'>Valider l'offre</a></td></tr><tr height='25'><td colspan='2' valign='top' align='right' style='color:black;font-size:12px'><b>Palier de Couverture 100 &euro;</b></td></tr><tr><td colspan='2' valign='top'><div id=\"price\" width=\"316px\" height=\"132px\" style=\"background-color:white;overflow:hidden\"></div></td></tr></table></div>";
	}
else
	{
	echo "<div id=\"price_cont\"  style=\"background-color:white;display: none;overflow:hidden;position: absolute;left: 8px;top: 230px;z-index: 99\"><table width=\"321px\" height=\"185px\" style=\"border: solid #3D3232 1px; moz-box-shadow: 8px 8px 12px #aaa; box-shadow: 8px 8px 12px #aaa; -webkit-box-shadow: 8px 8px 12px #aaa;\" ><tr height='25'><td><br></td><td><a href=\"#\" onclick=\"showCvtGrid(1);return false;\" style='position: absolute;right: 5px;top: 6px;color:white;background-color:#FF7F00;text-decoration:none;font-size:12px;padding:2px'>Fermer</a></td></tr><tr><td colspan='2' valign='top' align='right' style='color:black;font-size:12px'>&nbsp;</td></tr><tr height='25'><td colspan='2' valign='top'><div id=\"price\" width=\"316px\" height=\"132px\" style=\"background-color:white;overflow:hidden\"></div></td></tr></table></div>";
	}
echo "</td></tr>";
//style='color:white;background-color:#FF7F00;text-decoration:none;font-size:14px;padding:4px'>
?>	
	
<script>


mygrid_price = new dhtmlXGridObject('price');
mygrid_price.setStyle( "background-color:#FFFFFF;color:black;font-weight:bold;","","","");  
mygrid_price.setInitWidths("90,130,100");
	
mygrid_price.setHeader("Identifiant ,Date Couverture, Prix Couverture");
mygrid_price.attachFooter("<b>Nombre</b> ,,#stat_count");  
mygrid_price.setColTypes("ro,ro,edn");
mygrid_price.setColSorting("str,str,int");
mygrid_price.setColSorting("connector,connector,connector");
mygrid_price.setNumberFormat("0,000",2,","," ");
mygrid_price.setColValidators(",,Multiple100"); 
mygrid_price.init();
mygrid_price.enableSmartRendering(true,10);
mygrid_price.enableAlterCss("even","uneven");
mygrid_price.load("updateprice.php");
//mygrid_price.attachEvent("onEditCell",doOnEditCell);

var dp_price = new dataProcessor("updateprice.php");
dp_price.init(mygrid_price);
dp_price.defineAction ("inserted", myinsert);
//dp_price.defineAction ("updated", myupdate);
dp_price.enablePartialDataSend(true);
dp_price.defineAction("invalid",function(response){
    console.log('MAX : couverture');
	mygrid_price.clearAndLoad("updateprice.php");
    return false;
});

dhtmlxValidation.isMultiple100=function(data){
	 var ret = (data%100);
//	 console.log('MUL : '+ret);
    if ( ret == 0 ) { return true; }
	else {return false; }
	
};
mygrid_price.attachEvent("onValidationError", function(id,index,value,rule){
	var ret = (value%100);
	if ( ret < 50 )
		{
//		console.log('ERROR : ' + Math.round(value/100)*100);
		mygrid_price.cells(id,2).setValue(Math.round(value/100)*100);
		}
	else
		
		{
//		console.log('ERROR : ' + Math.ceil(value/100)*100);
		mygrid_price.cells(id,2).setValue(Math.ceil(value/100)*100);
		}
	
    return true;
});

function myinsert(tag){
	mygrid_price.clearAndLoad("updateprice.php");
//	mygrid_price.clearAndLoad("updateprice.php");
	return true;
}


</script>
<?php
}


function lstCotation($pdo,$vehicule_id,$immatriculation)
{
require("../config.php");
//function lst($pdo,$type,$value,$cotid,$ofbca,$ofcapvo)
echo"<table border='0' cellspacing='0' cellpadding='0'>
	<tr><td height='25' width='172'  bgcolor='#EEEEEE'>&nbsp;N&deg; Dossier : </td><td width='172' bgcolor='#EEEEEE'><select id='vehicule_id' name='vehicule_id' onchange=\"redir('vehicule_id',this.value)\">".lst($pdo,'vehicule_id',$vehicule_id,'','','')."</select></td></tr>
	<tr><td height='25' width='172'  bgcolor='#EEEEEE'>&nbsp;Immatriculation : </td><td width='172' bgcolor='#EEEEEE'><select id='immatriculation' name='immatriculation' onchange=\"redir('immatriculation',this.value)\">".lst($pdo,'immatriculation',$immatriculation,'','','')."</select></td></tr>
	<tr><td height='15' width='172'  bgcolor='#FFFFFF'></td><td width='172' bgcolor='#FFFFFF'></td></tr>

	</table>";

$sql="";
if ( $immatriculation != '' ) { $sql="select immatriculation, marque_nom, modele_nom, finition_nom, statut_id,mnt_bca,mnt_capvo,vehicule_id,date_demande,coteur_nom,km_livraison_final,date_livraison_final from vehicule_couverts v, vendeurs ve, coteurs co where ve.token_id = '".$_COOKIE['CAPVO_TOKEN']."' and v.vendeur_id= ve.vendeur_id and statut_id != '99' and statut_id != '98' and immatriculation='".$immatriculation."' and v.coteur_id = co.coteur_id order by date_demande desc;"; }
else if ( $vehicule_id != '' ){ $sql="select immatriculation, marque_nom, modele_nom, finition_nom, statut_id,mnt_bca,mnt_capvo,vehicule_id,date_demande,coteur_nom,km_livraison_final,date_livraison_final from vehicule_couverts v, vendeurs ve, coteurs co where ve.token_id = '".$_COOKIE['CAPVO_TOKEN']."' and v.vendeur_id= ve.vendeur_id and statut_id != '99' and statut_id != '98' and vehicule_id=".$vehicule_id." and v.coteur_id = co.coteur_id order by date_demande desc;"; }
else { $sql="select immatriculation, marque_nom, modele_nom, finition_nom, statut_id,mnt_bca,mnt_capvo,vehicule_id,date_demande,coteur_nom ,km_livraison_final,date_livraison_final from vehicule_couverts v, vendeurs ve, coteurs co where ve.token_id = '".$_COOKIE['CAPVO_TOKEN']."' and v.vendeur_id= ve.vendeur_id and statut_id != '99' and statut_id != '98' and date_demande > curdate()-180 and v.coteur_id = co.coteur_id order by date_demande desc;"; }

file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
$stm=$pdo->query($sql);

//$row=$stm->fetch();
//lst($pdo,$type,$value)
while (($row = $stm->fetch()) !== false)
		{
		
		echo "<tr><td colspan='3'><table border='0' cellspacing='0.2' cellpadding='5' bgcolor='#EEEEEE'>
	<tr><td height='25' width='172'>N&deg; Dossier : </td><td width='172'>".$row[7]."</td></tr>
	<tr><td height='25' >Immatriculation : </td><td>".$row[0]."</td></tr>
	<tr><td height='25' >Marque : </td><td>".$row[1]."</td></tr>
	<tr><td height='25' >Modele : </td><td>".$row[2]."</td></tr>
	<tr><td height='25' >Finition : </td><td>".$row[3]."</td></tr>
	<tr><td height='25' >Statut : </td><td><select id='statut_id' name='statut_id' onchange=\"updStatut(this.value,'updst','".$row[7]."')\">".lst($pdo,'statut',$row[4],$row[7],$row[5],$row[6])."</select></td></tr>
	<tr><td height='25' >Date Demande : </td><td>".$row[8]."</td></tr>
	<tr><td height='25' >Coteur : </td><td>".$row[9]."</td></tr>
	<tr><td height='25' >Km liv finale : </td><td><input type='number' id='km' onchange=\"updStatut(this.value,'updkm','".$row[7]."')\" value='".$row[10]."'></input></td></tr>
	<tr><td height='25' >Date liv finale : </td><td><input type='date' id='datelivraison' onchange=\"updStatut(this.value,'upddl','".$row[7]."')\" value='".$row[11]."'></input></td></tr>
	</table> <br>
	</td></tr>";
		}
		
}

function verifIdent($pdo)
{
require("../config.php");
if  ( $_COOKIE['CAPVO_TOKEN'] == '' )
	{

	echo "TOK".$_COOKIE['CAPVO_TOKEN']."<br>";
	identForm($pdo,'');
	}
else {
	// il faudrait lui redemmander l'identification toutes les semaines et etre compatible chrome
	$sql="select vendeur_nom, vendeur_prenom from vendeurs where token_id = '".$_COOKIE['CAPVO_TOKEN']."' and vendeur_statut !='parti' ;";
	file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
	//echo "TOK2".$_COOKIE['CAPVO_TOKEN']."<br>";	
	$stm=$pdo->query($sql) ; 
	$row = $stm->fetch();
	if ( $row[0] == '' )
		{
			echo "TOK".$_COOKIE['CAPVO_TOKEN']."<br>";
	
		identForm($pdo,'');
		}	
	else
		{
		//menuForm ($pdo);
		}
}	
}

function getCotation($pdo,$versionid,$year,$month,$km)
{
require("../config.php");
$query="select * from token_part where valid_date > now()";
$token="";
$stm=$pdo->query($query);

while (($row = $stm->fetch()) !== false)
		{
		$token = $row[0];
		}	

if ( $token == "") 
	{ // il faut regénérer un token
	$curl = curl_init();
$headers = array(
    'Username: ws.cobredia.fr',
	'Password: 6wc4QxSa9qHskr',
	'Content-Type: application/json; charset=utf-8'
);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_URL, 'https://apiv2.autobiz.com/users/v1/auth');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($curl);
	if(!$result){die("Connection Failure");}
	curl_close($curl);
	$obj = json_decode($result);
	$token=$obj->{'accessToken'};
	$query="truncate table token_part";
	$stm=$pdo->query($query);
	$query="insert into token_part values ('".$token."',date_add(now(),INTERVAL 58 MINUTE));";
	$stm=$pdo->query($query);
	}

$curl1 = curl_init();
$headers = array(
	'Authorization: Bearer '.$token
);


curl_setopt($curl1, CURLOPT_POST, 0);
curl_setopt($curl1, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl1, CURLOPT_URL, "https://apiv2.autobiz.com/quotation/v1/version/".$versionid."/year/".$year."/mileage/".$km."/quotation?month=".$month);
curl_setopt($curl1, CURLOPT_RETURNTRANSFER, 1);
 
$result1 = curl_exec($curl1);
if(!$result1){die("Connection Failure");}
curl_close($curl1);
//echo $result1;

$obj = json_decode($result1);

$array = json_decode(json_encode($obj->{'_quotation'}), true); 
$array1 = json_decode(json_encode($obj->{'_salesSpeed'}), true);
//return array( $array['autobiz'],$array['b2cMarketValue']);
return array( $array['autobiz'],$array['particular'],$array1['rotation']);
} 

function updCotation($pdo,$vehicule_id,$type,$data)
{
// UpDate Statut dans liste
require("../config.php");
$sql='';
if ( $type == 'updst' ) 
	{
	$sqlm="select statut_id from vehicule_couverts where vehicule_id ='".$vehicule_id."'";
	$stm=$pdo->query($sqlm); 
	$rowm=$stm->fetch();
	// Modification statut que si autobiz avant
//	echo $rowm[0];
	if ( $rowm[0] == '30' ) { $sql="update vehicule_couverts set statut_id ='".$data."', date_demande=now() where vehicule_id ='".$vehicule_id."'"; }
	}
	
//if ( $type == 'updkm' ) {$sql="update vehicule_couverts set km_livraison_final =".$data." where vehicule_id ='".$vehicule_id."'";}
//if ( $type == 'upddl' ) {$sql="update vehicule_couverts set date_livraison_final ='".$data."' where vehicule_id ='".$vehicule_id."'";}
$pdo->query($sql); 
file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);

}

function loadCotation($pdo)
{
require("../config.php");
// Création de la demande de cotation
// Update des infos : les images ont été préchargé en javascripts
//

$sqlm="select motorisation_id from motorisation_vehicule where motorisation_nom='".isNullM($_POST['motorisation'])."'";
file_put_contents($logfilew,$sqlm."\r\n",FILE_APPEND);
$stm=$pdo->query($sqlm); 
$rowm=$stm->fetch();


$finition1=explode ('==',$_POST['finition']);
//phpinfo();
//echo "FITINIT :".$_POST['finition'];
$date=explode ('-',$_POST['datemes']);
list($prixreprise,$prixpromarche,$rotation_autobiz)=getCotation($pdo,$finition1[0],$date[0],$date[1],$_POST['kmlivraison']);
if ( $prixpromarche == '' ) { $prixpromarche=0;}
if ( $prixreprise == '' ) { $prixreprise=0;}

//echo "FINIT : ".$prixpromarche .' :' .$prixreprise. ' : ';
if ( $prixreprise == '' ) { $prixreprise=0;}
$pourcentage=0;
if ( $prixreprise < 10000 ) { $pourcentage=10; }
if ( $prixreprise >= 10000 &&  $prixreprise < 20000) { $pourcentage=7; }
if ( $prixreprise >= 20000 ) { $pourcentage=5; }

$frais_calc=(($prixreprise*$pourcentage)/100);

$frais_vendeur=$_POST['frais'];
if ( $frais_vendeur == '' ) { $frais_vendeur=0; }
if ( $frais_vendeur < 0 ) { $frais_vendeur=$frais_vendeur*-1; }


//if ( $_POST['finition'] != '-' ) { $sqlinc.=", finition_nom = '".$_POST['finition']."'"; }

//
$_POST['immatriculation']=str_replace("-", "",$_POST['immatriculation']);
$sqlinc="";
if ( $_POST['marque'] != '' ) { $sqlinc .=", marque_nom = '".$_POST['marque']."'"; }
if ( $_POST['immatriculation'] != '' ) { $sqlinc.=", immatriculation = '".$_POST['immatriculation']."'"; } 
if ( $_POST['modele'] != '-' ) { $sqlinc.=", modele_nom = '".$_POST['modele']."'"; } 
if ( $_POST['motorisation'] != '-' ) { $sqlinc.=", motorisation_id = ".isNullM($rowm[0]); } 
if ( $_POST['finition'] != '-' ) { $sqlinc.=", finition_nom = '".$finition1[1]."'"; } 
if ( $_POST['couleur'] != '-' ) { $sqlinc.=", couleur_nom = '".$_POST['couleur']."'"; } 
if ( $_POST['kmlivraison'] != '' ) { $sqlinc.=", km_livraison = ".isNull($_POST['kmlivraison']); } 
if ( $_POST['kmactuel'] != '' ) { $sqlinc.=", km_actuel = ".isNull($_POST['kmactuel']); }
if ( $_POST['datemes'] != '' ) { 	//$dat=explode("/",$_POST['datelivraison']);
										$sqlinc.=", date_miseenservice = '".$_POST['datemes']."'"; 
									    //$sqlinc.=", date_livraison = '".$dat[2].'-'.$dat[1].'-'.$dat[0]."'"; 
										}
if ( $_POST['datemes'] == '' ) { 	//$dat=explode("/",$_POST['datelivraison']);
										$sqlinc.=", date_miseenservice = curdate()"; 
									    //$sqlinc.=", date_livraison = '".$dat[2].'-'.$dat[1].'-'.$dat[0]."'"; 
										}
if ( $_POST['datelivraison'] != '' ) { 	//$dat=explode("/",$_POST['datelivraison']);
										$sqlinc.=", date_livraison = '".$_POST['datelivraison']."'"; 
									    //$sqlinc.=", date_livraison = '".$dat[2].'-'.$dat[1].'-'.$dat[0]."'"; 
										}
if ( $_POST['datelivraison'] == '' ) { 	//$dat=explode("/",$_POST['datelivraison']);
										$sqlinc.=", date_livraison = curdate()"; 
									    //$sqlinc.=", date_livraison = '".$dat[2].'-'.$dat[1].'-'.$dat[0]."'"; 
										}

										
										

										
$_POST['remarque']=str_replace("'","\'",$_POST['remarque']);

$sqlinc.=", remarque = '".$_POST['remarque']."' "; 


$_POST['comveh']=str_replace("'","\'",$_POST['comveh']);
$_POST['comveh']="Frais Carrosserie : ".($_POST['fraiscarrosserie']*$prixcarrosserie)."\n"."Frais Jante : ".($_POST['fraisjante']*$prixjante)." \n"."\n"."Frais Embrayage : ".($_POST['fraisembrayage']*$prixembrayage)."\n"."Frais Distribution : ".($_POST['fraisdistribution']*$prixdistribution)."\n"."Frais Pneu : ".($_POST['fraispneu']*$prixpneu)."\n"."Frais Pare-Brise : ".($_POST['fraisparebrise']*$prixparebrise)."\n"."Frais Revision : ".($_POST['fraisrevision']*$prixrevision)."\n"."Frais Nettoyage : ".($_POST['fraisnettoyage']*$prixnettoyage)."\n"."Frais Divers : ".$_POST['fraisdivers']."\n"."Commentaire vehicule : ".$_POST['comveh']."\n";
$sqlinc.=", remarque_vehicule = '".$_POST['comveh']."'"; 

	
$sql="insert into commentaires (vehicule_id, commentaire_desc, date_commentaire, commentaire_type ) values ( ".$_POST['cotid'].", 'Frais Carrosserie : ".($_POST['fraiscarrosserie']*$prixcarrosserie)."\n"."Frais Jante : ".($_POST['fraisjante']*$prixjante)." \n"."\n"."Frais Embrayage : ".($_POST['fraisembrayage']*$prixembrayage)."\n"."Frais Distribution : ".($_POST['fraisdistribution']*$prixdistribution)."\n"."Frais Pneu : ".($_POST['fraispneu']*$prixpneu)."\n"."Frais Pare-Brise : ".($_POST['fraisparebrise']*$prixparebrise)."\n"."Frais Revision : ".($_POST['fraisrevision']*$prixrevision)."\n"."Frais Nettoyage : ".($_POST['fraisnettoyage']*$prixnettoyage)."\n"."Frais Divers : ".$_POST['fraisdivers']."',now(),'frais')";
$pdo->query($sql); 

$statut_id=30;
if ( $_POST['capvo'] =='on' ){$statut_id=1;}



$sql="update vehicule_couverts set vehicule_id ='".$_POST['cotid']."' ".$sqlinc." ,date_livraison_final='0000-00-00', statut_id=".$statut_id.",mnt_marche_autobiz=".$prixpromarche." , mnt_autobiz_orig=".$prixreprise." ,mnt_frais_vendeur=".$frais_vendeur."  where vehicule_id ='".$_POST['cotid']."'";
$pdo->query($sql); 
file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);

$sqlm="select round(datediff(date_livraison ,date_offre)/30),mnt_autobiz from vehicule_couverts where vehicule_id ='".$_POST['cotid']."'";
$stm=$pdo->query($sqlm); 
$rowm=$stm->fetch();


$offreautobiz=($prixreprise-$frais_calc);
if ($offreautobiz < 100 ) { $offreautobiz =100; }

// calcul du nombre de mois et des 
// supression du décalage de mois dans mreprise
$mreprise=(($offreautobiz*0.5)/100);
$mreprise=$mreprise*$rowm[0];

//somme de mois + 10%
$frais_calc+=$mreprise;
//retrait des 2 de l'offre de reprises
$mreprise=$prixreprise-$frais_calc-$frais_vendeur;

// arrondi centaine inferieur
$mreprise= $mreprise-(($mreprise%100));
$sreprise=explode ('.',$mreprise);


if ($sreprise[0] < 100 ) { $sreprise[0] =100; }


$sql="update vehicule_couverts set mnt_autobiz=".$sreprise[0]." ,mnt_frais_calc=".$frais_calc.", rotation_autobiz=".$rotation_autobiz." where vehicule_id ='".$_POST['cotid']."'";
file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
$pdo->query($sql); 




genOffAutobiz($pdo,$_POST['cotid']);
file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
file_put_contents($logfilew,"DATE :".$_POST['datelivraison']."\r\n",FILE_APPEND);
echo "<table bgcolor='#EEEEEE'><tr><td width='344'>Votre demande n : ".$_POST['cotid']." a &eacute;t&eacute; prise en compte, nous vous repondrons dans les meilleurs delais</td></tr></table>";
// envoie de mail au coteur que si CAPVO
if  ( $statut_id == '1' ) { sendMsg($_POST['cotid']); }
}


function genOffAutobiz($pdo,$cotid)
{
	
require("../config.php");
$sql="select v.vehicule_id,c.concession_nom,ve.vendeur_nom,co.coteur_nom,st.statut_libelle,date_format(date_offre,\"%d/%m/%Y\") date_offre,immatriculation,date_format(date_miseenservice,\"%d/%m/%Y\") date_miseenservice,v.marque_nom,v.modele_nom,motorisation_nom,v.finition_nom,v.couleur_nom,km_actuel,km_livraison,mnt_frais,origine,date_format(date_livraison,\"%d/%m/%Y\") date_livraison,mnt_bca,mnt_capvo,mnt_marchand,acheteur_nom,mnt_marche, vendeur,date_format(date_vente,\"%d/%m/%Y\") date_vente, date_envoieoffre, vendeur_email, coteur_email,ve.vendeur_id vehicule_link, mnt_autobiz from vehicule_couverts v, concessions c, vendeurs ve, coteurs co, statut_cotation st , motorisation_vehicule m  where c.concession_id=v.concession_id and co.coteur_id=v.coteur_id  and st.statut_id=v.statut_id and ve.vendeur_id=v.vendeur_id  and m.motorisation_id=v.motorisation_id   and v.statut_id != 99 and v.vehicule_id=".$cotid." order by date_demande desc";
$stm=$pdo->query($sql) ; 
$row = $stm->fetch();
file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
$date=date('d/m/y');
// Création de l'offre sous forme HTML	
$html="
<page backcolor='#FEFEFE' footer='date;heure;page' style='font-size: 12pt'>
<table height='3000' width='1000' border='0' cellspan='0' cellspacing='0'>
<tr width='800' bgcolor='#cccccc'><td height='80' width='400' align='center' valign='top'></td><td td width='300'></td><td width='200' align='center' valign='middle'></td></tr>
<tr width='800' bgcolor='#cccccc'><td height='50' width='400' align='center'>Le : ".$date." </td><td td width='300'>Indication de reprise AUTOBIZ</td><td width='200' align='center'>".$cotid."</td></tr>
<tr width='800' bgcolor='#cccccc'><td height='50' width='400' align='center'>Site : ".$row[1]." </td><td td width='300'></td><td width='200'  align='center'> Vendeur : ".$row[2]."  </td></tr>
<tr><td width='900' colspan='3'>
	<table border='1' cellspan='0' cellspacing='0'>
		<tr bgcolor='#ffffff' ><td height='50' width='300' align='center'>Immatriculation</td><td width='300' align='center'>Description</td><td width='300' align='center'>Indication AUTOBIZ</td></tr>
		<tr bgcolor='#eeeeee' ><td height='50' width='300' align='center'>".$row[6]."</td><td width='300' align='center'>".$row[8]."<br>".$row[9]."<br>".$row[10]."</td><td  width='300' align='center' bgcolor='#F7FF3C'>".$row[29]." &euro;</td></tr>
		<tr bgcolor='#ffffff' ><td height='50' width='300' align='center'>Date de livraison</td><td width='300' align='center'></td><td width='300' align='center'>Kilometrage de livraison</td></tr>
		<tr bgcolor='#eeeeee' ><td height='50' width='300' align='center'>".$row[17]."</td><td width='300' align='center'></td><td  width='300' align='center' >".$row[14]." </td></tr>
		
	</table>
</td></tr>
</table> 
</page>
";
 	$timestamp=time();
	// Convertion HTML vers PDF 
	try {
		if ( file_exists ($dirdoc.$cotid ) == FALSE ) { mkdir($dirdoc.$cotid, 0777); }
		$html2pdf = new Html2Pdf('L', 'A4', 'fr', true, 'UTF-8', array(30, 30, 10, 10));
		$html2pdf->pdf->SetDisplayMode('fullpage');
		$html2pdf->writeHTML($html);
		
		$html2pdf->output($dirdoc.$cotid.'/16_offre_'.$cotid.'_'.$timestamp.'.pdf','F'); 
   
	} catch (Html2PdfException $e) {
		$html2pdf->clean();
		$formatter = new ExceptionFormatter($e);
		echo $formatter->getHtmlMessage();
	}

	$com=$_POST['commentaire'];
	$com=str_replace("'","\'",$com);
	$com=str_replace("\"","\"",$com);
	// Envoi du PDF
	$email = new PHPMailer();
	$email->SetFrom('autobiz@cap-vo.fr', 'AUTOBIZ'); //Name is optional 
	$email -> CharSet = "UTF-8"; 
	
	$email->Subject   = 'Offre AUTOBIZ Immatriculation : '.$row[6] .' dossier : '.$id;
	$email->Body      = "Bonjour,\n ci-joint l'offre AUTOBIZ\nBonne Journee\n\n\n";
	//echo "Email :".$row[26];
	$email->AddAddress( $row[26]);
	$file_to_attach = $dirdoc.$cotid.'/16_offre_'.$cotid.'_'.$timestamp.'.pdf';
	$email->AddAttachment( $file_to_attach , 'offre.pdf' );
	$email->Send();
	$sql="insert into commentaires (vehicule_id,commentaire_desc, date_commentaire,commentaire_type,vendeur_id ) values (".$row[0].",'Envoie Offre AUTOBIZ\n\n',now(),'vendeur',".$row1[5].");";
	$stm=$pdo->query($sql) ; 
	
}

function sendMsg($cotid)
{
	$email = new PHPMailer();
	$email->SetFrom('couverture@cap-vo.fr', 'CAP VO'); //Name is optional
	$email -> CharSet = "UTF-8"; 
	$email->Subject   = 'Offre CAP VO Numero  : '.$cotid ;
	$email->Body      = "Bonjour,\n Une nouvelle cotation a ete demande N : ".$cotid." \n Merci de la prendre en compte\n\n Bonne Journee\n\n CAP VO";
	$email->AddAddress( 'couverture@cap-vo.fr' );
	//$email->AddAddress( 't.jouy@cobredia.bzh' );
	$email->Send();
}
function isNull($num)
{
if ( $num == '' ) { $num=0;}
if ( $num == '-' ) { $num=0;}
return $num;
}

function isNullM($num)
{
if ( $num == '' ) { $num=1;}
if ( $num == '-' ) { $num=1;}
return $num;
}


function menuForm($pdo)
{
echo "<form name='form' id='form' method=\"post\">
<table>
<tr><td colspan='2' height='35'><a href='#' onclick=\"document.location.href='./index.php?f=lst';\">Liste des cotations</a></td></tr>
<tr><td colspan='2' height='35'><a href='#' onclick=\"document.location.href='./index.php?f=new';\">Nouvelle Cotation</a></td></tr>
</table>";
//</form>";
}


function identForm($pdo,$msg)
{

//if ( $_POST['connecter'] == 'Connecter' ) { echo "!!! Mot de passe incorrect 1";}
if ( $msg == '' ) { $msg="Merci de vous identifier"; }
echo "<form  name='form' id='form' method=\"post\"><table width='344' border='0' bgcolor='#DDDDDD'>
<tr><td colspan='2' height='35' >".$msg."<br><br></td></tr>
<tr><td height='35'>Email : </td><td><input type='text' name='email' width='245'></input></td></tr>
<tr><td height='35'>Mot de passe : </td><td><input type='password' name='mdp'></input></td></tr>
<tr><td><br><br><input type='submit' name='connecter' value='Connecter'></input></td></tr>
<tr><td colspan='2'><br><br><br><a href='#' onclick=\"document.location.href='./index.php?f=mdp';\">Mot de passe oubli&eacute;</a></td></tr>
</table>
</form>";
}

function mdpForm($pdo,$msg)
{
require("../config.php");
if ( $_POST['send'] == 'Envoyer mot de passe' ) 
	{ 
	sendMdp($pdo,$_POST['email']);
	identForm($pdo,$msg);
	}
else	
	{
	echo "<form name='form' id='form' method=\"post\"><table width='345' border='0' bgcolor='#DDDDDD'>
<tr><td height='35' colspan='2'>Merci de saisir votre identifiant</td></tr>
		
<tr><td height='35'>Identifiant : </td><td><input type='text' name='email'></input></td></tr>
<tr><td><br><br><input type='submit' name='send' value='Envoyer mot de passe'></input></td></tr>
</table>
</form>";
	}
}


function sendMdp($pdo,$addemail)
{
require("../config.php");
//MDP Vendeurs
$sql="select vendeur_mdp from vendeurs where vendeur_email = lower('".$addemail."') ;";

file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
$stm=$pdo->query($sql) ; 
$row=$stm->fetch(); 
if ( $row[0] != '')
		{
		$email = new PHPMailer();
		$email->SetFrom('couverture@cap-vo.fr', 'CAP VO'); //Name is optional
		$email -> CharSet = "UTF-8"; 
		$email->Subject   = 'Mot de passe CAP VO' ;
		$email->Body      = "Bonjour,\nVotre mot de passe est : ".$row[0]." \n\n Bonne Journee\n\n CAP VO";
		$email->AddAddress( 'couverture@cap-vo.fr' );
		$email->AddAddress( $addemail);
		$email->Send();
		echo "Mot de passe envoy&eacute;"; 
		}
// MDP MARCHANDS
$sql="select vendeur_mdp from marchand_vendeurs where vendeur_email = lower('".$addemail."') ;";
//echo $sql;
file_put_contents($logfilew,$sql."\r\n",FILE_APPEND);
$stm=$pdo->query($sql) ; 
$row=$stm->fetch(); 
if ( $row[0] != '')
		{
		$email = new PHPMailer();
		$email->SetFrom('couverture@cap-vo.fr', 'CAP VO'); //Name is optional
		$email -> CharSet = "UTF-8"; 
		$email->Subject   = 'Mot de passe CAP VO' ;
		$email->Body      = "Bonjour,\nVotre mot de passe est : ".$row[0]." \n\n Bonne Journee\n\n CAP VO";
		$email->AddAddress( 'couverture@cap-vo.fr' );
		$email->AddAddress( $addemail);
		$email->Send();
		echo "Mot de passe envoy&eacute;"; 
		}
}



 function passgen2($nbChar)
{
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCEFGHIJKLMNOPQRSTUVWXYZ0123456789'),1, $nbChar); 
}


function lstm($pdo,$type,$value,$cotid,$ofbca,$ofcapvo,$retenu,$encours)
{
$sql='';
if ( $retenu == 'checked' )
	{
	if ( $type == 'vehicule_id' )	{ $sql=" select distinct v.vehicule_id, v.vehicule_id,substr(v.modele_nom,1,8), upper(v.immatriculation)  from commentaires c, vehicule_couverts v, marchand_vendeurs m, marchands m1  where  m.marchand_id=c.marchand_id  and m.marchand_id=m1.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and  statut_id  in (4,7,9,10,8)  and  v.acheteur_nom=m1.marchand_nom order by date_demande desc";	}
	else if ( $type == 'immatriculation' )	{ $sql=" select distinct v.immatriculation, v.immatriculation,substr(v.modele_nom,1,8), v.immatriculation  from commentaires c, vehicule_couverts v, marchand_vendeurs m, marchands m1  where m.marchand_id=c.marchand_id and m.marchand_id=m1.marchand_id  and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and  statut_id in (4,7,9,10,8) and v.acheteur_nom=m1.marchand_nom order by date_demande desc";	}
	}
	// Modif TJO
else if ( $encours == 'checked' )
	{
	if ( $type == 'vehicule_id' )	{ $sql=" select distinct v.vehicule_id, v.vehicule_id,substr(v.modele_nom,1,8), upper(v.immatriculation)  from commentaires c, vehicule_couverts v, marchand_vendeurs m, marchands m1  where  m.marchand_id=c.marchand_id  and m.marchand_id=m1.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."'  and date_couverture >= ADDDATE(now(),interval -3 DAY )   order by date_demande desc";	}
	else if ( $type == 'immatriculation' )	{ $sql=" select distinct v.immatriculation, v.immatriculation,substr(v.modele_nom,1,8), v.immatriculation  from commentaires c, vehicule_couverts v, marchand_vendeurs m, marchands m1  where m.marchand_id=c.marchand_id and m.marchand_id=m1.marchand_id  and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."'  and date_couverture >= ADDDATE(now(),interval -3 DAY )  order by date_demande desc";	}
	}

else	
	{
	if ( $type == 'vehicule_id' )	{ $sql=" select distinct v.vehicule_id, v.vehicule_id,substr(v.modele_nom,1,8), upper(v.immatriculation) from commentaires c, vehicule_couverts v, marchand_vendeurs m, marchands m1  where  m.marchand_id=c.marchand_id  and m.marchand_id=m1.marchand_id and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and  statut_id not in (99,6) and ( date_couverture >=  adddate(now(),interval -5 DAY) or  v.acheteur_nom=m1.marchand_nom ) order by date_demande desc";	}
	else if ( $type == 'immatriculation' )	{ $sql=" select distinct v.immatriculation, v.immatriculation,substr(v.modele_nom,1,8), v.immatriculation from commentaires c, vehicule_couverts v, marchand_vendeurs m, marchands m1  where m.marchand_id=c.marchand_id and m.marchand_id=m1.marchand_id  and v.vehicule_id=c.vehicule_id and m.token_id ='".$_COOKIE['CAPVO_TOKEN']."' and  statut_id not in (99,6) and  ( date_couverture >=  adddate(now(),interval -5 DAY) or  v.acheteur_nom=m1.marchand_nom ) order by date_demande desc";	}
	}
file_put_contents("/tmp/webappd.log",$encours." :" .$sql."\r\n",FILE_APPEND);
$stm=$pdo->query($sql);
$str="";

$str.="<option value='-' >-</option>"; 
							
foreach ( $stm->fetchAll() as $row )
	{ 
	$check='';
	if ( $value == $row[1] ) { $check ='selected';}
	if ( $type == 'statut' )	{ $dis='disabled';
								if ( $ofbca < $ofcapvo && $row[1] == 4 ) { $dis =''; }
								if ( $row[1] == 1 ) { $dis =''; $row[0]='Demande Cotation CAPVO'; }
							
								//if ( $ofbca >= $ofcapvo && $row[1] == 5 ) { $dis ='';}
								//if ( $row[1] == 6 ) { $dis ='';}
								$str.="<option value='".$row[1]."' $check $dis>".$row[0]."</option>"; 
								}
	else {	$str.="<option value='".$row[1]."' $check>".$row[3]." - "  .$row[2]." - "  .$row[0]."</option>"; }
	}

return ($str);
}

function lst($pdo,$type,$value,$cotid,$ofbca,$ofcapvo)
{
$sql='';
if ( $type == 'marque' ) {	$sql="select marque_nom, marque_nom from marque where marque_nom !='' order by marque_nom";	}
else if ( $type == 'couleur' )	{ $sql="select couleur_nom, couleur_nom from couleur_vehicule where couleur_nom !='' order by couleur_nom";	}
else if ( $type == 'motorisation' )	{ $sql="select motorisation_nom,motorisation_id from motorisation_vehicule where motorisation_nom !='' order by motorisation_nom";	}
else if ( $type == 'statut' )	{ $sql="select statut_libelle,statut_id from statut_cotation where statut_id not in (99) order by statut_id";	}
else if ( $type == 'vehicule_id' )	{ $sql="select vehicule_id, vehicule_id from  vehicule_couverts v, vendeurs ve where token_id = '".$_COOKIE['CAPVO_TOKEN']."' and v.vendeur_id= ve.vendeur_id and date_demande > curdate()-180 and statut_id != 99 order by date_demande desc";	}
else if ( $type == 'immatriculation' )	{ $sql="select immatriculation, immatriculation from  vehicule_couverts v, vendeurs ve where token_id = '".$_COOKIE['CAPVO_TOKEN']."' and v.vendeur_id= ve.vendeur_id and date_demande > curdate()-180 and statut_id != 99 order by date_demande desc";	}

$stm=$pdo->query($sql);
$str="";
foreach ( $stm->fetchAll() as $row )
	{ 
	$check='';
	if ( $value == $row[1] ) { $check ='selected';}
	if ( $type == 'statut' )	{ $dis='disabled';
								if ( $ofbca < $ofcapvo && $row[1] == 4 ) { $dis =''; }
								if ( $row[1] == 1 ) { $dis =''; $row[0]='Demande Cotation CAPVO'; }
							
								//if ( $ofbca >= $ofcapvo && $row[1] == 5 ) { $dis ='';}
								//if ( $row[1] == 6 ) { $dis ='';}
								$str.="<option value='".$row[1]."' $check $dis>".$row[0]."</option>"; 
								}
	else {	$str.="<option value='".$row[1]."' $check>".$row[0]."</option>"; }
	}
//file_put_contents("/tmp/webapp.log",$sql."\r\n",FILE_APPEND);
return ($str);
}
function newCotation ($pdo)
{
require("../config.php");
// récupération des infos du vendeur, nom, prenom concession	
$sql="select vendeur_nom, vendeur_prenom, concession_id,vendeur_id from vendeurs where token_id = '".$_COOKIE['CAPVO_TOKEN']."' and vendeur_statut !='parti' ;";

$stm=$pdo->query($sql) ; 
$row = $stm->fetch();	
file_put_contents($logfilew,$_COOKIE['CAPVO_TOKEN']." : " .$sql."\r\n",FILE_APPEND);

// création du véhicule en base
$app='w';
//echo "V:". $_COOKIE['CAPVO_WEBAPP_VERSION'];
if ( $_COOKIE['CAPVO_WEBAPP_VERSION'] != '') { $app='w1'; }
$sql1="insert into vehicule_couverts (concession_id,coteur_id,vendeur_id,statut_id,date_offre,date_demande,marque_id,modele_id,motorisation_id,finition_id,couleur_id,webapp,date_vendu) values (".$row[2].",1,".$row[3].",98,curdate(),now(),1,1,1,1,1,'".$app."','0000-00-00');";
//echo "SQL : ".$sql1;
$pdo->query($sql1); 
file_put_contents($logfilew,$_COOKIE['WEBAPP_CAPVO_VERSION']." : " .$sql1."\r\n",FILE_APPEND);

// récupération de l'id véhicule 
$sql2="select max(vehicule_id) from  vehicule_couverts where concession_id='".$row[2]."' and vendeur_id='".$row[3]."' ;";
$stm2=$pdo->query($sql2) ; 
$row2 = $stm2->fetch(); 
$d=date("Ymd-G:i:s");
file_put_contents($logfilew,$d." : Creation dossier N&deg;: ".$row2[0]." par le vendeur : ".$row[0]." de la concession ".$row[2]."\r\n",FILE_APPEND);

// creation du dossier de sotckage des documents pour la cotation
$uploaddir = $dirdoc.$row2[0];
if ( file_exists ($uploaddir ) == FALSE ) { mkdir($uploaddir, 0777); }	
//echo "<form method=\"post\" name='form' id='form' enctype='multipart/form-data'>

/*<td>&nbsp;&nbsp;<progress id=\"file_avant\" value=\"0\" max=\"100\"> 0% </progress></td>*/
echo "<form method=\"post\" name='form' id='form'>
<input id=\"cotid\" name=\"cotid\" type=\"hidden\" value= \"".$row2[0]."\">

<table border ='0' cellspacing='0' bgcolor='#EEEEEE' width='100%'>
<tr><td height='35' colspan='2' align='center'><br>Merci de bien vouloir remplir l'ensemble de ces informations pour un indication de prix AUTOBIZ et/ou une cotation CAPVO
<input type='hidden' id='motorisation' name='motorisation' ></input>
<input type='hidden' id='modele' name='modele' ></input>
<input type='hidden' id='couleur' name='couleur' ></input>
<input type='hidden' id='marque' name='marque' ></input>
<br><br></td></tr>

<tr><td height='35' width='224'>N&deg; Dossier : </td><td width='188'>".$row2[0]."</td></tr> 

<tr><td height='35'>Immatriculation : </td><td><input type='text' id='immatriculation' name='immatriculation' onchange='javascript:getInfoVehicule(this.value);'  style='width:180px;'></input></td></tr>

<tr><td height='35' >Marque : </td><td><div id='marque_div' name='marque_div' ></div></td></tr>
<tr><td height='35'>Mod&egrave;le : </td><td><div id='modele_div' name='modele_div' ></div></td></tr>
<tr><td height='35'>Motorisation : </td><td><div id='motorisation_div' name='motorisation_div' ></div></td></tr>
<tr><td height='35'>Finition : </td><td><select id='finition' name='finition' style='width:180px;' onchange='javascript:check_val();'></select></td></tr>
<tr><td height='35'>Couleur : </td><td><div id='couleur_div' name='couleur_div' ></div></td></tr>
<tr><td height='35'>KM Actuel : </td><td><input type='number' id='kmactuel' name='kmactuel' onchange='javascript:check_val();' style='width:180px;'></input></td></tr>
<tr><td height='35'>Date MEC : </td><td><input type='date' id='datemes' name='datemes' onchange='javascript:check_val();' style='width:180px;'></input></td></tr>

<tr><td height='35'>KM Livraison : </td><td><input type='number' id='kmlivraison' name='kmlivraison' onchange='javascript:check_val();' style='width:180px;'></input></td></tr>
<tr><td height='35'>Date Livraison : </td><td><input type='date' id='datelivraison' name='datelivraison' onchange='javascript:check_val();' style='width:180px;'></input></td></tr>
<tr><td height='35'>Frais RE : </td><td><input type='number' id='frais' name='frais' style='width:180px;' value='600' readonly></input></td></tr>

<tr><td height='45'>Carrosserie :</td><td> <img src='/capvo/img/moins.jpg' onClick='moins(\"fraiscarrosserie\",".$prixcarrosserie.");' style=\"vertical-align: top; width:40px;height:40px\">&nbsp;<input type='number' id='fraiscarrosserie' name='fraiscarrosserie' style='width:30px;height:40px;vertical-align: top;' value='0' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'  onClick='plus(\"fraiscarrosserie\",".$prixcarrosserie.");' style=\"vertical-align: top; width:40px; height:40px\">&nbsp;&nbsp;<input type='number' id='fraiscarrosseriet' style='width:55px;height:40px;vertical-align: top;' value='0' readonly></input></td></tr>
<tr><td height='45'>Jante : </td><td> <img src='/capvo/img/moins.jpg' onClick='moins(\"fraisjante\",".$prixjante.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;<input type='number' id='fraisjante' name='fraisjante' style='width:30px;height:40px;vertical-align: top;' value='0' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'  onClick='plus(\"fraisjante\",".$prixjante.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;&nbsp;<input type='number' id='fraisjantet' style='width:55px;height:40px;vertical-align: top;' value='0' readonly></input></td></tr>
<tr><td height='45'>Embrayage : </td><td> <img src='/capvo/img/moins.jpg' onClick='moins(\"fraisembrayage\",".$prixembrayage.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;<input type='number' id='fraisembrayage' name='fraisembrayage' style='width:30px;height:40px;vertical-align: top;' value='0' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'  onClick='plus(\"fraisembrayage\",".$prixembrayage.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;&nbsp;<input type='number' id='fraisembrayaget' style='width:55px;height:40px;vertical-align: top;' value='0' readonly></input></td></tr>
<tr><td height='45'>Distribution : </td><td> <img src='/capvo/img/moins.jpg' onClick='moins(\"fraisdistribution\",".$prixdistribution.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;<input type='number' id='fraisdistribution' name='fraisdistribution' style='width:30px;height:40px;vertical-align: top;' value='0' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'  onClick='plus(\"fraisdistribution\",".$prixdistribution.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;&nbsp;<input type='number' id='fraisdistributiont' style='width:55px;height:40px;vertical-align: top;' value='0' readonly></input></td></tr>
<tr><td height='45'>Pneu : </td><td> <img src='/capvo/img/moins.jpg' onClick='moins(\"fraispneu\",".$prixpneu.");' style=\"vertical-align: top; width:40px; height:40px\">&nbsp;<input type='number' id='fraispneu' name='fraispneu' style='width:30px;height:40px;vertical-align: top;' value='0' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'  onClick='plus(\"fraispneu\",".$prixpneu.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;&nbsp;<input type='number' id='fraispneut' style='width:55px;height:40px;vertical-align: top;' value='0' readonly></input></td></tr>
<tr><td height='45'>Pare-Brise : </td><td> <img src='/capvo/img/moins.jpg' onClick='moins(\"fraisparebrise\",".$prixparebrise.");' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;<input type='number' id='fraisparebrise' name='fraisparebrise' style='width:30px;height:40px;vertical-align: top;' value='0' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'  onClick='plus(\"fraisparebrise\",".$prixparebrise.");' style=\"vertical-align: top; width:40px; height:40px\">&nbsp;&nbsp;<input type='number' id='fraisparebriset' style='width:55px;height:40px;vertical-align: top;' value='0' readonly></input></td></tr>

<tr><td height='45'>R&eacute;vision : </td><td> <img src='/capvo/img/moins.jpg'  style=\"vertical-align: top; width:40px; height:40px\">&nbsp;<input type='number' id='fraisrevision' name='fraisrevision' style='width:30px;height:40px;vertical-align: top;' value='1' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'   style=\"vertical-align: top; width:40px; height:40px\">&nbsp;&nbsp;<input type='number' id='fraisrevisiont' style='width:55px;height:40px;vertical-align: top;' value='500' readonly></input></td></tr>
<tr><td height='45'>Nettoyage : </td><td> <img src='/capvo/img/moins.jpg' style=\"vertical-align: top;  width:40px;height:40px\">&nbsp;<input type='number' id='fraisnettoyage' name='fraisnettoyage' style='width:30px;height:40px;vertical-align: top;' value='1' readonly></input>&nbsp;<img src='/capvo/img/plus.jpg'   style=\"vertical-align: top; width:40px; height:40px\">&nbsp;&nbsp;<input type='number' id='fraisnettoyaget' style='width:55px;height:40px;vertical-align: top;' value='100' readonly></input></td></tr>
<tr><td height='45'>Divers : </td><td> <input type='number' id='fraisdivers' name='fraisdivers' style='width:180px;vertical-align: top;' value='0' onChange='updatefrais();'> </input></td></tr>
<tr><td height='75'>Commentaire V&eacute;hicule : </td><td><textarea id='comveh' name='comveh' rows=\"3\" cols=\"22\"></textarea></td></tr>
"; 

$form_images = array(
	"avant" => array(
		"title" => "Photo Avant"
	),
	"droite" => array(
		"title" => "Photo Droite"
	),
	"arriere" => array(
		"title" => "Photo Arriere"
	),
	"gauche" => array(
		"title" => "Photo Gauche"
	),
	"int1" => array(
		"title" => "Photo Int 1"
	),
	"int2" => array(
		"title" => "Photo Int 2"
	),
	"cg" => array(
		"title" => "Carte Grise"
	),
	"ct" => array(
		"title" => "Cont Tech"
	),
	"rep" => array(
		"title" => "Estimation"
	),
	"car1" => array(
		"title" => "Divers 1",
		"show_next" => true
	),
	"car2" => array(
		"title" => "Divers 2",
		"show_next" => true,
		"hidden" => "car1"
	),
	"car3" => array(
		"title" => "Divers 3",
		"show_next" => true,
		"hidden" => "car2"
	),
	"car4" => array(
		"title" => "Divers 4",
		"show_next" => true,
		"hidden" => "car3"
	),
	"car5" => array(
		"title" => "Divers 5",
		"show_next" => true,
		"hidden" => "car4"
	),
	"car6" => array(
		"title" => "Divers 6",
		"show_next" => true,
		"hidden" => "car5"
	),
	"car7" => array(
		"title" => "Divers 7",
		"show_next" => true,
		"hidden" => "car6"
	),
	"car8" => array(
		"title" => "Divers 8",
		"show_next" => true,
		"hidden" => "car7"
	),
	"car9" => array(
		"title" => "Divers 9",
		"show_next" => true,
		"hidden" => "car8"
	),
	"car10" => array(
		"title" => "Divers 10",
		"show_next" => true,
		"hidden" => "car9"
	)
);

?>

<?php foreach($form_images as $id => $infos): ?>
<?php $tr_classes=sprintf("block-image-field block_photo_%s %s",
	$id, 
	(isset($infos['hidden']) && $infos['hidden']) ? "hidden hidden-by-" . $infos['hidden'] : ""); ?>
<tr class="<?= $tr_classes ?>">
	<td height="45">
		<?= $infos["title"] ?>: &nbsp;
		<button type="button" class="BoutonPhoto hidden" data:field_id="<?= $id ?>" id="<?= $id ?>-bt_photo">
		</button>
	</td>

	<td align="center">
		<label for="photo_<?= $id ?>" class="label-file">
			&nbsp;&nbsp;&nbsp;&nbsp;<img src="/capvo/img/app_photo.png"
			height="40px">
		</label>
	<input type="file" id="photo_<?= $id ?>"
	  class="input-file image-input <?= (isset($infos['show_next']) && $infos['show_next']) ? "show-next" : "" ?>"
	  accept="image/*"
	  style="display:none" name="photo_<?= $id ?>" />
  </td>
</tr>
<tr class="<?= $tr_classes ?>">
	<td height="5" colspan="2" align="right">
		<div id="<?= $id ?>" class="message-zone"></div>
	</td>
</tr>
<tr class="<?= $tr_classes ?>">
	<td colspan="2" >
		<div class="Previsualisation NotLoaded"></div>
	</td>
</tr>

<?php endforeach; ?>

<?php echo "

<tr><td>Commentaire Coteur : </td><td><textarea id='remarque' name='remarque' rows=\"5\" cols=\"22\"></textarea></td></tr>
<tr><td height='25' colspan='2'></td></tr>
<tr><td height='35' colspan='2'><b>Demande Cotation CAPVO :</b><input type='checkbox' id='capvo' name='capvo' style='width:60px;'></input></td></tr>
<tr><td colspan='2'><input type='submit' name='Soumettre' disabled='true'  id='Soumettre' value='Soumettre la demande' onClick=\"sendSock('insert','".$row2[0]."')\"></input></td></tr>

</table>

</form>";

}

file_put_contents($logfilew,$_COOKIE['CAPVO_TOKEN']."\r\n",FILE_APPEND);

?>

</td></tr>
</table>
<script>

/*
document.getElementById('prev_photo_car2').style.display = 'none';
document.getElementById('file_car2').style.display = 'none';
document.getElementById('ph_car2').style.display = 'none';

document.getElementById('prev_photo_car3').style.display = 'none';
document.getElementById('file_car3').style.display = 'none';
document.getElementById('ph_car3').style.display = 'none';

document.getElementById('prev_photo_car4').style.display = 'none';
document.getElementById('file_car4').style.display = 'none';
document.getElementById('ph_car4').style.display = 'none';

document.getElementById('prev_photo_car5').style.display = 'none';
document.getElementById('file_car5').style.display = 'none';
document.getElementById('ph_car5').style.display = 'none';

document.getElementById('prev_photo_car6').style.display = 'none';
document.getElementById('file_car6').style.display = 'none';
document.getElementById('ph_car6').style.display = 'none';

document.getElementById('prev_photo_car7').style.display = 'none';
document.getElementById('file_car7').style.display = 'none';
document.getElementById('ph_car7').style.display = 'none';

document.getElementById('prev_photo_car8').style.display = 'none';
document.getElementById('file_car8').style.display = 'none';
document.getElementById('ph_car8').style.display = 'none';

document.getElementById('prev_photo_car9').style.display = 'none';
document.getElementById('file_car9').style.display = 'none';
document.getElementById('ph_car9').style.display = 'none';

document.getElementById('prev_photo_car10').style.display = 'none';
document.getElementById('file_car10').style.display = 'none';
document.getElementById('ph_car10').style.display = 'none';
*/

</script>
</html> 

