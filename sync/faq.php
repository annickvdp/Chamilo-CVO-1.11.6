<?php
require_once('../main/inc/global.inc.php');
require_once('../main/inc/lib/main_api.lib.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Frequently Asked Questions (FAQ)</title>
<style type="text/css" media="screen, projection">
/*<![CDATA[*/
<?php
$my_style = api_get_setting('stylesheets');
$my_code_path = api_get_path(WEB_CODE_PATH);
if(empty($my_style)){$my_style = 'default';}
echo '@import "../main/css/'.$my_style.'/default.css";'."\n";
?>
/*]]>*/
</style>
<style type="text/css">
html, body{
margin:10px;
}
div.vraag{
margin:0px 0px 10px 0px;
font-weight:bold;
}
div.antwoord{
font-size:11px;
display:none;

margin:0px 0px 20px 0px;
}
a, li,p{
font-size:11px;
padding:0px;
margin:0px;
font-weight:normal;
}
a.intlink{
color:#000000;
font-weight:normal;
text-decoration:underline;
}
ol, ul{
font-size:11px;
margin:0px 0px 0px 25px;
}
</style>
<script type="text/javascript">
var storedDiv = null;
function getDiv(oID) {
if(document.getElementById) {
return document.getElementById(oID);
} else if( document.all ) {
return document.all[oID];
} else { return null; }
};
function toggleInfo(oID) {
var oDiv = getDiv(oID); if( !oDiv ) { return; }
oDiv.style.display = (oDiv.style.display=='none') ? 'block' : 'none';
if( storedDiv && storedDiv != oDiv ) { storedDiv.style.display = 'none';
} storedDiv = oDiv;
};
window.onload = function () {
var webadres = null;
var temp_adres = new Array();
var vraag = new Array();

for( var i = 0, y; y = getDiv('ans'+i); i++ ) {
y.style.display = 'none';
}
webadres = document.URL;
temp_adres = webadres.split('#');
if(typeof temp_adres[1] != 'undefined'){
//alert(temp_adres[1]);
vraag = temp_adres[1].split('g');
//alert(vraag[1]);
toggleInfo('ans'+vraag[1]);
}
};
</script>
</head>

<body>
<h1>Frequently Asked Questions (FAQ)</h1>
<h2>Kennismaken met Dokeos</h2>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans0');"><a href="#">Wat is Dokeos? </a></div>
<div class="antwoord" id="ans0">
CVO Dokeos is een digitaal leerplatform: een webapplicatie waar docenten per cursus studiemateriaal en opdrachten kunnen aanbieden en waar studenten on-line met elkaar en met docenten kunnen communiceren.<br /><br />
Ook het inleveren van uitgewerkte opdrachten en maken van zelftoetsen kan on-line gebeuren. Er is plaats voor persoonlijke informatie, cursusinformatie, cursusinhoud, communicatie, discussie, samenwerken aan opdrachten, etc. 

</div>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans1');"><a href="#">Wat heb ik nodig om met Dokeos te kunnen werken?</a></div>
<div class="antwoord" id="ans1">
<p>Voor dokeos.cvoleuven.be heb je nodig: </p>
<ol id="faq_ol">
<li>een  account van CVO Leuven - Landen (krijg je bij je inschrijving),</li>
<li>een computer met internetverbinding,</li>
<li>een browser die cookies en javascript ondersteunt, bv. Firefox.</li>
</ol>
</div>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans2');"><a href="#">Hoe kan ik het Dokeos platform bereiken? </a></div>
<div class="antwoord" id="ans2">
De elektronische leeromgeving van CVO Leuven - Landen is bereikbaar vanop elke computer via deze internetverbinding: <a class="intlink" href="http://dokeos.cvoleuven.be" target="_blank">http://www.cvoleuven.be/dokeos</a></div>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans4');"><a href="#">Wie kan inloggen in het systeem?</a></div>
<div class="antwoord" id="ans4">
Elke student, docent, personeelslid verbonden aan CVO Leuven - Landen kan inloggen in de elektronische leeromgeving. 
</div>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans6');"><a href="#">Ik heb geen inloggegevens ontvangen, ben ze verloren of ik weet ze niet meer. Wat nu?</a></div>
<div class="antwoord" id="ans6">
<!--<p>Klik op de startpagina op de link "Wachtwoord vergeten". Vul je e-mailadres in.</p>
<p>Krijg je de melding 'Geen gebruiker gekend met dit e-mailadres.', neem dan contact op met het secretariaat van CVO Leuven - Landen</p>
--> Neem contact met <a href="mailto:dokeos@cvoleuven.be">dokeos@cvoleuven.be</a></div>
<!-- -->
<h2>Algemeen</h2>
<div class="vraag" onclick="toggleInfo('ans8');"><a href="#">Hoe wijzig ik mijn e-mailadres voor Dokeos? </a></div>
<div class="antwoord" id="ans8">
<ul>
<li>log in Dokeos</li>
<li>ga naar de rubriek &quot;Mijn profiel&quot;</li>
<li>vul in het invulvak 'E-mail' je e-mailadres in</li>
<li>klik op de knop "OK"</li>
</ul>
</div>

<div class="vraag" onclick="toggleInfo('ans7');"><a href="#">Hoe wijzig ik mijn wachtwoord voor Dokeos? </a></div>
<div class="antwoord" id="ans7">
<ul>
<li>log in Dokeos</li>
<li>ga naar de rubriek &quot;Mijn profiel&quot;</li>
<li>vul in het invulvak 'Wachtwoord' een nieuw wachtwoord in</li>
<li>confirmeer je nieuwe wachtwoord in het invulvak 'Bevestiging'</li>
<li>klik op de knop "OK"</li>
</ul>
</div>
<!-- -->
 
<div class="vraag" onclick="toggleInfo('ans12');"><a href="#">Moet ik na gebruik van Dokeos uitloggen? </a></div>
<div class="antwoord" id="ans12">
We raden iedereen aan om na het gebruik van Dokeos uit te loggen. <br />
Zeker als je een computer gebruikt in een computerlokaal of bij iemand anders. <br />
Je kunt uitloggen door te klikken op "Logout" rechtsbovenaan het scherm.
</div>
<!-- -->
 
<div class="vraag" onclick="toggleInfo('ans13');"><a href="#">Ik ondervind problemen met bestandsnamen in Dokeos.</a></div>
<div class="antwoord" id="ans13">
Dokeos past in principe de bestandsnamen aan om de inherente beperkingen van PHP te omzeilen.  <br />
Gebruik bij voorkeur enkel <span style="font-weight: bold">cijfers en letters</span>, vermijd spaties, speciale tekens en accenten.
</div>
 
<!-- -->
<div class="vraag" onclick="toggleInfo('ans11');"><a href="#">Waar moet ik op letten als ik een firewall heb op mijn PC? </a></div>
<div class="antwoord" id="ans11">
Als je computer uitgerust is met een softwarematige firewall en bepaalde <br />
zaken niet werken zoals het hoort dan doe je best een configuratiewijziging.<br /><br />

In de meeste firewall paketten is er de mogelijkheid om sites aan de "trusted zone" (vertrouwde omgeving) toe te voegen.<br />
Deze instelling is meestal te vinden onder Zones > Toevoegen (Add) > ...<br />
Als je de ingebouwde firewall onder Windows XP SP2 gebruikt, dan is het <br />
niet mogelijk om via de firewall instellingen de site toe te voegen.<br>
Het moet via Internet Explorer gebeuren.<br>
<br>
1. Start Internet Explorer<br>
2. Klik op Extra > internet Opties<br>
3. Klik op het tabblad beveiliging<br>
4. Klik op het vertrouwde websites (trusted sites) icoon<br>
5. Serververificatie (https) uitvinken<br>
6. Klik op de websites knop<br>
7. Voeg dokeos.cvoleuven.be toe<br>
8. Sluit de vensters door op OK te klikken<br>
9. Herstart de browser (alle vensters sluiten en opnieuw opstarten)<br><br>
Voor software-specifieke info raadpleeg je best de handleiding van je firewall.
</div>

<!-- -->
<div class="vraag" onclick="toggleInfo('ans60');"><a href="#">Waarom kan ik een docx bestand (Word 2007) niet openen? </a></div>
<div class="antwoord" id="ans60">
Docx (Word 2007) documenten kunnen alleen geopend worden als je het programma Microsoft Word, <br />of OpenOffice heb. Als dit het geval is en je nog problemen heb <a href="http://vps09win2003.futureweb.be/courses/S1/document/Problemen_met_Internet_Explorer_8_en_Word_2007.pdf?cidReq=S1">klik hier</a> voor meer informatie.
</div>
<!-- -->
<!-- -->
<h2>Studenten</h2>
<!-- -->
<!-- -->
<div class="vraag" onclick="toggleInfo('ans61');"><a href="#">Er ontbreken cursussen in de rubriek "Mijn cursussen" in Dokeos.</a></div>
<div class="antwoord" id="ans61">Meestal komt het omdat je cursus nog niet geactiveerd is. Dan moet je even geduld hebben.<br /><br />
Mogelijk problemen waardoor je al je cursussen niet te zien krijgt: 
<UL><LI>	je inschrijvingsgeld is nog niet betaald of er is niet voldoende betaald,
<LI>	er ontbreken attesten, 
<LI>	alle inschrijvingen zijn nog niet administratief verwerkt,
<LI>	de synchronisatie met onze databank nog niet gebeurd is,
<LI>	je ben voor een verkeerde cursus ingeschreven

</UL>
</div>
 <h2>Leerkrachten</h2>
  
<div class="vraag" onclick="toggleInfo('ans62');"><a href="#">Wat zijn cursusquota en hoe ga ik daarmee om? </a></div>
<div class="antwoord" id="ans62">
Het standaardquotum voor een cursus is 50 Megabyte. Als je dit quotum bereikt, zal je geen bestanden meer kunnen toevoegen aan je cursus.
We raden in de meeste gevallen aan om Microsoft-Word, Microsoft-Excel en Microsoft-Powerpoint bestanden om te zetten in pdf formaat. Zo worden deze bestanden vlot 5 à 10x kleiner.
Via de gratis software PdfCreator of CutePDF kan elke Microsoft-Windows gebruiker op eenvoudige wijze pdf-files aanmaken.
OpenOffice kan trouwens met 1 druk op de knop PDF's aanmaken. Linux en Mac OS X gebruikers kunnen vanuit elk programma printen naar PDF-bestanden.
Indien echt nodig kan je een verhoging van het quotum vragen aan dokeos@cvoleuven.be.

</div>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans63');"><a href="#">Er ontbreken cursussen in de rubriek "Mijn cursussen" in Dokeos.</a></div>
<div class="antwoord" id="ans63">
Meestal komt het omdat je de cursus nog niet geactiveerd hebt. <br /><br />Dan moet je de cursus onder ‘cursusactivatie’ activeren. Indien je de cursus hier niet terug vindt, contacteer dan dokeos@cvoleuven.be.</div>
<!-- -->
<div class="vraag" onclick="toggleInfo('ans10');"><a href="#">Studenten krijgen mijn cursus niet te zien.</a></div>
<div class="antwoord" id="ans10">
Meestal komt het omdat je de cursus nog niet geactiveerd hebt. <br /><br /> Dan moet je de cursus onder ‘cursusactivatie’ activeren. Na cursusactivatie moet je wachten tot er een synchronisatie met onze databank gebeurd is. Deze synchronisatie gebeurt twee keer per dag om 1 uur en om 13 uur. <br /><br />
Het kan ook zijn dat per ongeluk de cursus onzichtbaar gemaakt werd. Ga naar Cursuseigenschappen om te verifiëren of de zichtbaarheid op 'beperkte toegang' staat.<br /><br />

Mogelijk problemen waardoor de student al zijn cursussen niet te zien krijgt: 
<UL>
<LI>	het inschrijvingsgeld van de student is nog niet betaald of er is niet voldoende betaald,</LI>
<LI>	er ontbreken attesten, </LI>
<LI>	alle inschrijvingen zijn nog niet administratief verwerkt,</LI>
<LI>	de synchronisatie met onze databank nog niet gebeurd is,</LI>
<LI>	de cursist is voor een verkeerde cursus ingeschreven</LI>
</UL>
</div><br /><br />
</BODY></HTML>