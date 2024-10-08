<?php
/**
 * DokuWiki Plugin ismsaddons (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  François KAAG <francois.kaag@cardynal.fr>
 */
use dokuwiki\Utf8\Clean;
class syntax_plugin_ismsaddons_riskindicators extends \dokuwiki\Extension\SyntaxPlugin
{
    public function __construct() {
	$this->triples =& plugin_load('helper', 'strata_triples');
        $this->pscope = $this->getConf('param');
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
                $property = Clean::deaccent($this->getLang($key));
                $table= $this->triples->fetchTriples($scope.$property,null,null,null);
                foreach ($table as $elem)
                {
                        if ($elem['predicate']!=$this->triples->getConf('title_key')) { $res[$elem['predicate']]=$elem['object']; };
                }
                asort ($res,SORT_NUMERIC);
                return $res;
        }

        function getValue ($subject,$property) {
                $result = null;
                $predicate = $this->getLang($property);
                $triple = $this->triples->fetchTriples($subject,$predicate,null,null);
                if ($triple) {
                        $result = intval($triple[0]['object']);
                }
                return $result;

	}
	
	function printRow ($R,$tcrit,$label,$table,$lcolor)
	{		
		$R->doc .="<tr><td>".$label."</td>";
		
		foreach ($tcrit as $crit)
		{
			$val = $table[$crit];
			$style=($lcolor?" style='background-color:#".$lcolor[$val]."'":"");
			$R->doc .="<td".$style.">".$val."</td>";
		}		
		$R->doc .="</tr>";		
	}
	
    public function render($mode, Doku_Renderer $R, $data) {
	global $ID;

	if ($this->pscope == '')
	{
		$scope = GetNS ($ID);
                $scope = ($scope == ''?'param#':$scope.':param#');
	}
	else $scope = $this->pscope.'#';

	if($mode == 'xhtml') {
	
		$tcrit = $this->getParam('criterion',$scope);
		$tlevel = $this->getParam('RiskLevel',$scope);
		$tcolor = $this->getParam('RiskColor',$scope);
		
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
			$trisk = $this->triples->fetchTriples(null,$this->getLang('criterion'),$crit,null);
			$N = count($trisk);
			$sumc = $qsumc = 0;
			$sumf = $qsumf = 0;
			$cmaxc = $cmaxf = 0;
			
			foreach ($trisk as $erisk)
			{
				$riskId = $erisk['subject'];
				$grav = $this->getValue($riskId,'impact');
				$VcR = $this->getValue($riskId,'cl');
				$VfR = $this->getValue($riskId,'fl');
				$risklevelc = $grav*$VcR;
				$risklevelf = $grav*$VfR;				

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
		
		$this->printRow($R,$tcrit,$this->getLang('NRiskLabel'),$NRisk,null);
		$this->printRow($R,$tcrit,$this->getLang('MaxCLabel'),$MaxC,$lcolor);
		$this->printRow($R,$tcrit,$this->getLang('MeanCLabel'),$MeanC,$lcolor);
		$this->printRow($R,$tcrit,$this->getLang('QMeanCLabel'),$QMeanC,$lcolor);
		$this->printRow($R,$tcrit,$this->getLang('MaxFLabel'),$MaxF,$lcolor);
		$this->printRow($R,$tcrit,$this->getLang('MeanFLabel'),$MeanF,$lcolor);
		$this->printRow($R,$tcrit,$this->getLang('QMeanFLabel'),$QMeanF,$lcolor);
		$R->doc .="</tbody></table>";
		return true;
   }
	return false;
    }
}
