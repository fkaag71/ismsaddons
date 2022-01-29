<?php
/**
 * DokuWiki Plugin ismsaddons (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  François KAAG <francois.kaag@cardynal.fr>
 */
class syntax_plugin_ismsaddons_riskindicators extends \dokuwiki\Extension\SyntaxPlugin
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
        $this->Lexer->addSpecialPattern('~~RISKINDICATORS~~', $mode, 'plugin_ismsaddons_riskindicators');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();
        return $data;
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
	
	function printRow ($R,$tcrit,$label,$table,$lcolor)
	{		
		$R->doc .="<tr><td>".$label."</td>";
		
		foreach ($tcrit as $crit)
		{
			$val = $table[$crit];
			$style=($lcolor?" style='background-color:#".$lcolor[$val]."'":"");
			$R->doc .="<td".$style.">".$val.$cellformat."</td>";
		}		
		$R->doc .="</tr>";		
	}
	
    public function render($mode, Doku_Renderer $R, $data) {
	global $ID;
	$scope = GetNS ($ID);
	
	if($mode == 'xhtml') {
	
		$tcrit = $this->getParam("Critere",$scope);
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
		
		foreach ($tcrit as $label=>$crit)
		{			
			$trisk = $this->triples->fetchTriples(null,"critère",$crit,null);
			$N = count($trisk);
			$sumc = $qsumc = 0;
			$sumf = $qsumf = 0;
			$cmaxc = $cmaxf = 0;
			
			foreach ($trisk as $erisk)
			{
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
				$risklevelc = $grav*$vcmax;
				$risklevelf = $grav*$vfmax;
				
				if ($risklevelc > $cmaxc) $cmaxc = $risklevelc;
				if ($risklevelf > $cmaxf) $cmaxf = $risklevelf;
				
				$sumc += $risklevelc;
				$qsumc+= $risklevelc*$risklevelc;
				$sumf += $risklevelf;
				$qsumf+= $risklevelf*$risklevelf;		
			}
			$NRisk[$crit]= $N;
			$MaxC[$crit] = $cmaxc;
			$MeanC[$crit] = round($sumc / $N,1);
			$QMeanC[$crit] = round(sqrt($qsumc/ $N),1);
			$MaxF[$crit] = $cmaxf;
			$MeanF[$crit] = round($sumf / $N,1);
			$QMeanF[$crit] = round(sqrt($qsumf/ $N),1);
		}
				
		$R->doc .="<style>
table {
	border-collapse: collapse;
	text-align: center;
	vertical-align: center;
}
td {
    border: 1px solid #333;
	overflow: hidden;
}
th,
tfoot,
td:first-child {
	font-weight: bold;
	text-align: center;
    background-color: grey;
    color: #fff;
	}
}
</style>";

		$R->doc .= "<table><thead><tr><th></th>";
		foreach ($tcrit as $label=>$crit)
		{
			$R->doc .="<th>".$label."</th>";
		}
		$R->doc .="</tr></thead><tbody>";
		
		$this->printRow($R,$tcrit,"Nombre de risques",$NRisk,null);
		$this->printRow($R,$tcrit,"Niveau max courant",$MaxC,$lcolor);
		$this->printRow($R,$tcrit,"Niveau moyen courant",$MeanC,$lcolor);
		$this->printRow($R,$tcrit,"Niveau quadratique courant",$QMeanC,$lcolor);
		$this->printRow($R,$tcrit,"Niveau max futur",$MaxF,$lcolor);
		$this->printRow($R,$tcrit,"Niveau moyen futur",$MeanF,$lcolor);
		$this->printRow($R,$tcrit,"Niveau quadratique futur",$QMeanF,$lcolor);
		$R->doc .="</tbody></table>";
		return true;
   }
	return false;
    }
}
