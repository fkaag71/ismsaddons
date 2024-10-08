<?php
/**
 * DokuWiki Plugin ismsaddons (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  François KAAG <francois.kaag@cardynal.fr>
 */

use dokuwiki\Utf8\Clean;
class syntax_plugin_ismsaddons_risktable extends \dokuwiki\Extension\SyntaxPlugin
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
        $this->Lexer->addSpecialPattern('~~RISKTABLE~~', $mode, 'plugin_ismsaddons_risktable');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();
        return $data;
    }

	function getParam ($key,$scope) {
		$table= $this->triples->fetchTriples($scope.$key,null,null,null);
		foreach ($table as $elem)
		{
			if ($elem['predicate']!=$this->triples->getConf('title_key')) { $res[$elem['predicate']]=$elem['object']; };
		}		
		asort ($res,SORT_NUMERIC);
		return $res;
	}

	function getValue ($subject,$predicate) {
		$result = null;
		$triple = $this->triples->fetchTriples($subject,$predicate,null,null);
		if ($triple) {
			$result = intval($triple[0]['object']);
		}
		return $result;
}
	
    public function render($mode, Doku_Renderer $R, $data) {
	global $ID;

	$scope = GetNS ($ID);
	if ($this->pscope == '') $pscope = ($scope == ''?'param#':$scope.':param#');
 	else $pscope = $this->pscope.'#';

	if($mode == 'xhtml') {
		
		$tgrav = $this->getParam(Clean::deaccent($this->getLang('impact')),$pscope);		
		$tvrai = $this->getParam(Clean::deaccent($this->getLang('likelihood')),$pscope);
		$tlevel = $this->getParam(Clean::deaccent($this->getLang('RiskLevel')),$pscope);
		$tcolor = $this->getParam(Clean::deaccent($this->getLang('RiskColor')),$pscope);
		
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
			$riskId = $erisk['subject'];
			$rname = noNS($riskId);
			$grav = $this->getValue($riskId,$this->getLang('impact'));
			$VcR = $this->getValue($riskId,$this->getLang('cl'));
			$VfR = $this->getValue($riskId,$this->getLang('fl'));

			$VC[$grav][$VcR][]=$rname;
			$VF[$grav][$VfR][]=$rname;			
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
                $base =strtok($_SERVER['REQUEST_URI'],'?');
		$R->doc .= "<table><tbody>";
		foreach (array_reverse($tgrav) as $glabel =>$grav)
		{
			$R->doc .="<tr><td>".$glabel."</td>";
			foreach ($tvrai as $vrai)
			{				
				$R->doc.="<td style='background-color:#".$lcolor[$grav*$vrai]."'>";	

				$rnames=[];
				if (isset($VC[$grav])) { 
                                   if (isset($VC[$grav][$vrai])) {$rnames=(array)$VC[$grav][$vrai]; } }

				foreach ( $rnames as $rname)
				{
						$R->doc .='<span class="risk present"><a href="'.$base.'?id='.$scope.':'.$rname.'" class="rlink" >'.$rname.'</a></span>';
				}

				$rnames=[];
				if (isset($VF[$grav])) { 
                                   if (isset($VF[$grav][$vrai])) {$rnames=(array)$VF[$grav][$vrai]; } }

				foreach ($rnames as $rname)
				{
						$R->doc .='<span class="risk future"><a href="'.$base.'?id='.$scope.':'.$rname.'" class="rlink">'.$rname.'</a></span>';
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
		$R ->doc .= "<span class='risk present'>".$this->getLang('present')."</span> <span class='risk future'>".$this->getLang('future')."</span>";
		
		return true;
   }
	return false;
    }
}
