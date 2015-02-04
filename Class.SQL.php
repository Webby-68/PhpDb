<?
// ********** Code by Nicolas Randé : nicolas.rande@gmail.com ********** //
// https://github.com/Webby-68/PhpDb/

class SQL
	{
  	var $DBConnexion=NULL;
	var $BaseServer='localhost';
	var $Connected=false;
	var $Charset='utf-8';

	var $debug=false;
	var $NbRequetes=0;
	var $NbRequetesSelect=0;
	var $NbRequetesInsert=0;
	var $NbRequetesUpdate=0;
	var $NbRequetesCache=0;

	var $tabRequetes=NULL;
	var $logRequetes=false;
	var $ExecTime=0;

	// =================================================================================================== //
    function SQL($BaseName='',$Login='root',$Password='')
		{
		if ($this->debug) echo '[Object] SQL('.$BaseName.','.$Login.','.$Password.')';
		if ($BaseName!='')	$this->BaseName=$BaseName;
		$this->Login=$Login;
		$this->Password=$Password;
		if ($Password=='')
			{
			$this->Login='root';
			$this->Password='';
			}
		}
	// =================================================================================================== //


	// =================================================================================================== //
	function connexion()
		{
		if (!$this->Connected)
			{
			//$this->debug=true;
			if ($this->debug)	echo '<p class="Debug">mysql_connect('.$this->BaseServer.','.$this->Login.',######);</p>';
			$this->DBConnexion=@mysql_connect($this->Server,$this->Login,$this->Password,true);
			if ($this->DBConnexion==true)
				{
				if ($this->debug)	echo '<p class="Debug">mysql_select_db('.$this->BaseName.','.$this->DBConnexion.');</p>';
				$DBSelection=@mysql_select_db($this->BaseName, $this->DBConnexion);
				if (!$DBSelection)
					{
					echo '<p class="Erreur">Echec de connexion &agrave; la base de donn&eacute;es sur "'.$this->BaseName.'@'.$this->BaseServer.'"</p>';
					exit();
					return false;
					}
				//if ($this->debug)	echo '<p class="Debug">Ressource mySQL : '.$this->DBConnexion.'</p>';
				//if ($this->debug)	echo '<p class="Debug">Charset : '.$this->Charset.' ('.$this->BaseName.')</p>';
				if ($this->Charset=='utf-8')	@mysql_query('SET NAMES \'utf8\';',$this->DBConnexion);
				$this->Connected=true;
				return true;
				}
			else
				{
				echo '<p class="Erreur">Echec de connexion de "'.$this->Login.'" &agrave; la base de donn&eacute;es sur "'.$this->BaseName.'@'.$this->BaseServer.'"</p>';
				exit();
				return false;
				}
			}
		return true;
		}
	// =================================================================================================== //


	// =================================================================================================== //
	function deconnexion()
		{
		if ($this->debug)	echo '<p class="Debug">mysql_close('.$this->DBConnexion.');</p>';
		mysql_close($this->DBConnexion);
		$this->Connected=false;
		}
	// =================================================================================================== //
    
	
	// =================================================================================================== //
	function select($Requete,$debug=false)
		{
		$this->connexion();
		if ($this->debug || $debug)	echo '<p class="Debug">=> Fonction select('.htmlentities($Requete,ENT_COMPAT,$this->Chaset).')</p>';

		// Execution de la requete
		$mt=getmicrotime();
		$resultat=@mysql_query($Requete,$this->DBConnexion);

		if (mysql_error() || !$resultat)
			{
			echo '<p class="Erreur"><strong>Erreur de la requ&ecirc;te sur la base "'.$this->BaseName.'" :</strong><br />'.htmlentities($Requete,ENT_COMPAT,$this->Chaset).'<br /><em>'.mysql_error($this->DBConnexion).'</em></p>';
			exit();
			}
		
		// On met les différents enregistrements de la requete dans un tableau PHP
		$tab=array();
		while ($ligne=@mysql_fetch_array($resultat,MYSQL_ASSOC)) $tab[]=$ligne;
		if ($this->debug)	echo '<p class="Debug">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&raquo; retourne '.count($tab).' enregistrement(s)</p>';

		// On libèe la mémoire du resultat de la dernière requete
		@mysql_free_result($resultat);

		$mt=(getmicrotime()-$mt)*1000;
		if ($this->logRequetes)	$this->tabRequetes[]='['.number_format($this->ExecTime,1,',',' ').'ms + '.number_format($mt,1,',',' ').'ms] '.$Requete;
		$this->ExecTime+=$mt;

		if (mysql_error())
			{
			echo '<p class="Erreur"><strong>Erreur mysql :</strong> '.htmlentities(mysql_error($this->DBConnexion),ENT_COMPAT,$this->Chaset).'</p>';
			exit();
			}

		//Comptage du nombre de requetes
		$this->NbRequetes++;
		$this->NbRequetesSelect++;

		// On retourne le tableau, ou false si la requete a éhoué.
		if ($resultat!=false)	return $tab;
		else					return false;
		}
	// =================================================================================================== //


	// =================================================================================================== //
    function update($Requete,$debug=false)
		{
		$this->connexion();

		if ($this->debug || $debug)	echo '<p class="Log">'.htmlentities($Requete,ENT_COMPAT,$this->Chaset).'</p>';

		// Execution de la requete
		$mt=getmicrotime();
		$resultat=@mysql_query($Requete,$this->DBConnexion);
		$mt=getmicrotime()-$mt;
		if ($this->logRequetes)	$this->tabRequetes[]='['.number_format($mt*1000,1,',',' ').'ms] '.$Requete;
		$this->ExecTime+=$mt;

		if (mysql_error() || !$resultat)
			{
			echo '<p class="Erreur"><strong>Erreur de la requ&ecirc;te :</strong><br />'.htmlentities($Requete,ENT_COMPAT,$this->Chaset).'<br /><em>'.mysql_error($this->DBConnexion).'</em></p>';
			exit();
			}

		//Comptage du nombre de requetes
		$this->NbRequetes++;
		$this->NbRequetesUpdate++;

		// On retourne un booléen exprimant la réussite ou l'echec de la requete SQL.
		if ($resultat!=false)	return true;
		else					return false;
		}
	// =================================================================================================== //
    

	// =================================================================================================== //
    function insert($Requete,$debug=false)
		{
		$this->connexion();
		if ($this->debug || $debug)	echo '<p class="Debug">=> Fonction insert('.htmlentities($Requete,ENT_COMPAT,$this->Chaset).')</p>';

		// Execution de la requete
		$mt=getmicrotime();
		$resultat=@mysql_query($Requete, $this->DBConnexion);
		$mt=getmicrotime()-$mt;
		if ($this->logRequetes)	$this->tabRequetes[]=$Requete.' ['.$mt.']';
		$this->ExecTime+=$mt;

		if (mysql_error() || !$resultat)
			{
			echo '<p class="Erreur"><strong>Erreur de la requete :</strong><br />'.htmlentities($Requete,ENT_COMPAT,$this->Chaset).'<br /><em>'.mysql_error($this->DBConnexion).'</em></p>';
			exit();
			}

		//Comptage du nombre de requetes
		$this->NbRequetes++;
		$this->NbRequetesInsert++;

		if ($this->debug)	echo '<p class="Debug">=&gt; Fonction dernierIdentifiant()</p>';

		if ($resultat!=false)	return mysql_insert_id($this->DBConnexion);
		else					return false;				
		}
	// =================================================================================================== //
	}    








?>
