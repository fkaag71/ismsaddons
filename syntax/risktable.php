<?php
/**
 * DokuWiki Plugin ismsaddons (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  François KAAG <francois.kaag@cardynal.fr>
 */
class syntax_plugin_ismsaddons_risktable extends \dokuwiki\Extension\SyntaxPlugin
{
    public function __construct() {
	$this->triples =& plugin_load('helper', 'strata_triples');
	}

    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getPType()
    {
        return 'normal';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 30;
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('~~RISKTABLE~~', $mode, 'plugin_ismsaddons_risktable');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();
        return $data;
    }

	function icmp($a,$b)
	{
		$d = intval($a) - intval($b);
		return ($d>0)-($d<0);
	}

	function getParam ($key,$scope) {
		$table= $this->triples->fetchTriples($scope.":param#".$key,null,null,null);
		foreach ($table as $elem)
		{
			if ($elem['predicate']!='entry title') { $res[$elem['predicate']]=$elem['object']; };
		}		
		asort ($res,SORT_NUMERIC);
		return $res;
	}
	
    public function render($mode, Doku_Renderer $R, $data) {
	global $ID;
	$scope = GetNS ($ID);
	
	if($mode == 'xhtml') {
		
		$tgrav = $this->getParam("gravite",$scope);		
		$tvrai = $this->getParam("vraisemblance",$scope);
		$tlevel = $this->getParam("NiveauRisque",$scope);
		$tcolor = $this->getParam("CouleurRisque",$scope);
		
		$i = 1;
		foreach ($tlevel as $level=>$limit)
		{
			for (;$i<=$limit;$i++)
			{
				$lcolor[$i]=$tcolor[$level];
			}
		}
		
		$trisk = $this->triples->fetchTriples(null,"is a","risk",null);	
		
		foreach ($trisk as $erisk)
		{
			$rname = noNS($erisk['subject']);
			$trgrav = $this->triples->fetchTriples($erisk['subject'],"gravité",null,null);
			if ($trgrav) {
				$grav= intval($trgrav[0]['object']);
			};
						
			$vcmax = $vfmax = 0;
			$tscn = $this->triples->fetchTriples($erisk['subject'],"scenarios",null,null);
			foreach ($tscn as $escn)
			{
				$tvc = $this->triples->fetchTriples($escn['object'],"vc",null,null);
				if ($tvc) { 
					$vc = intval($tvc[0]['object']); 
					if ($vc > $vcmax) $vcmax = $vc;
					}
				$tvf = $this->triples->fetchTriples($escn['object'],"vf",null,null);
				if ($tvf) { 
					$vf = intval($tvf[0]['object']); 
					if ($vf > $vfmax) $vfmax = $vf;
					}			
			}
			$VC[$grav][$vcmax][]=$rname;
			$VF[$grav][$vfmax][]=$rname;			
		}
		
		$R->doc .="<style>
table {
	border-collapse: collapse;
	width:100%;
	text-align: center;
	vertical-align: center;
	table-layout: fixed;
}
td {
    border: 1px solid #333;
	overflow: hidden;
	height: 120px;
}
th,
tfoot,
td:first-child {
	font-weight: bold;
	text-align: center;
    background-color: grey;
    color: #fff;
}
.risk {
	padding-left: 10px;
	padding-right: 10px;
	border-radius: 25px;
	margin-top: 4px;
	margin-bottom: 4px;
	display: inline-block;
	color:white;
}
.rlink:any-link {
	color:white;
}
.future {
	background-color:green;
}
a#risk {
	color:white;
}
.present {
	background-color: blue;
}
</style>";
		$R->doc .= "<table><tbody>";
		foreach (array_reverse($tgrav) as $glabel =>$grav)
		{
			$R->doc .="<tr><td>".$glabel."</td>";
			foreach ($tvrai as $vrai)
			{				
				$R->doc.="<td style='background-color:#".$lcolor[$grav*$vrai]."'>";	
				
				foreach ((array)$VC[$grav][$vrai] as $rname)
				{
						$R->doc .='<span class="risk present"><a href="/doku/doku.php?id='.$scope.':'.$rname.'" class="rlink" >'.$rname.'</a></span>';
				}
				foreach ((array)$VF[$grav][$vrai] as $rname)
				{
						$R->doc .='<span class="risk future"><a href="/doku/doku.php?id='.$scope.':'.$rname.'" class="rlink">'.$rname.'</a></span>';
				}				
				$R->doc.="</td>";
			}
			$R->doc .="</tr>";
		}
		$R->doc .="</tbody><tfoot><th></th>";
		foreach ($tvrai as $vlabel => $vrai)
		{
			$R->doc .="<th>".$vlabel."</th>";
		}
		$R->doc .="</tfoot></table>";
		$R ->doc .= "<span class='risk present'>Niveaux présents</span> <span class='risk future'>Niveaux futurs</span>";
		
		return true;
   }
	return false;
    }
}
