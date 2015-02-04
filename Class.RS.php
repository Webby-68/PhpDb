 <?
// ********** Code by Nicolas Randé : nicolas.rande@gmail.com ********** //
// https://github.com/Webby-68/PhpDb/

$cache=array(); // Gestion d'un cache (sera fait plus tard)

include_once('Class.SQL.php');

// Exemples d'utilisation :
// $objSQL=new SQL('myDbName','mydbLogin','myDbPass','myServeurIP');
// $objRS=new RS('Utilisateur');
// $objRS->load(75);	// cherche l'enregistrement correspondant à Utilisateur.NumUtilisateur=75
// var_dump($objRS);	// affiche les valeurs de ce recordset récupérées dans l'objet
// echo $objRS->Nom;	// Affichage de la valeur d'un champs du recordset
// $objRS->Nom='Toto';	// modification de la valeur dans l'objet
// $objRS->Prenom='Titi';
// $objRS->save();	// sauvegarde dans la base (sorte de commit quoi)

// $objRS=new RS('Utilisateur');
// $new=$objRS->create(); // crée un nouvel enregistrement (et accessoirement retourne l'ID auto)
// $objRS->Nom='Toto';
// $objRS->Prenom='Tata';
// $objRS->save();	// sauve les infos en base

// $objRS=new RS('Utilisateur');
// $objRS->load(74);	// cherche l'enregistrement correspondant à Utilisateur.NumUtilisateur=74
// $objRS->delete();	// Supprime le recordset

// Fonctionne aussi (et surtout) dans le cadre d'un héritage de classe //

class DBTable
{
var $dbTable='';
var $tabChamps=array();
var $debug=false;

function DBTable($Table='',$Base='')
	{
	if ($Table!='')	$this->dbTable=$Table;
	$this->dbBase=$Base;
	$this->Table='`'.$this->dbTable.'`';
	if ($this->dbBase!='')	$this->Table='`'.$this->dbBase.'`.'.$this->Table;
	}

function create($debug=false)
	{
	global $objSQL;
	if ($this->debug || $debug) echo '[Object] DBTable("'.$this->dbTable.'")->load('.$Num.')<br />';

	$this->tabChamps=array();
	$this->Num=0;
	if ($this->dbTable!='')
		{
		$sql='INSERT INTO '.$this->Table.' (DateCreation) VALUES (NOW());';
		$this->Num=$objSQL->insert($sql);
		$this->load($this->Num);
		}
	return $this->Num;
	}

function load($Num=0,$debug=false)
	{
	global $objSQL;
	$Num=intval($Num);
	if ($this->debug || $debug) echo '[Object] DBTable("'.$this->dbTable.'")->load('.$Num.')<br />';

	$this->tabChamps=array();
	$this->Num=0;
	if ($Num>0 && $this->dbTable!='')
		{
		$sql='SELECT * FROM '.$this->Table.' WHERE Num'.$this->dbTable.'='.$Num.' LIMIT 0,1;';
		$tab=$objSQL->select($sql);
		if (is_array($tab[0]))
		 	{
			$this->Num=$Num;
			foreach ($tab[0] as $k=>$v)
				{
				$this->tabChamps[]=$k;
				$this->$k=$v;
				}
			}
		}
	}

function save($debug=false)
	{
	global $objSQL;
	if ($this->debug || $debug) echo '[Object] DBTable("'.$this->dbTable.'")->save('.$this->Num.')<br />';

	if ($this->Num>0 && $this->dbTable!='')
		{
		$Params='';
		foreach ($this->tabChamps as $c)
			{
			if ($c=='DateModification')	$Params.=',`'.$c.'`=NOW()';
			else						$Params.=',`'.$c.'`='.mysql_real_escape_string($this->$c);
			}
		$Params=substr($Params,1);
		$sql='UPDATE '.$this->Table.' SET '.$Params.' WHERE Num'.$this->dbTable.'='.$this->Num.';';
		$objSQL->update($sql);
		}
	}

function json()
	{
	$s='';
	if ($this->Num>0 && $this->dbTable!='')
		{
		foreach ($this->tabChamps as $c)
			{
			if ($s!='')	$s.=',';
			$s.='"'.json_encode($c).'":"'.json_encode($this->$c).'"';
			}
		}
	return '{'.$s.'}';
	}

function delete($debug=false)
	{
	global $objSQL;
	if ($this->debug || $debug) echo '[Object] DBTable('.$this->Num.')->delete()<br />';

	if ($this->Num>0 && $this->dbTable!='')
		{
		$sql='DELETE FROM '.$this->Table.' WHERE Num'.$this->dbTable.'='.$this->Num.';';
		$objSQL->update($sql);
		}
	}

}

?>
