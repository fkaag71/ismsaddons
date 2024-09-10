<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * DokuWiki Plugin ismsaddons (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author François KAAG <francois.kaag@cardynal.fr>
 */
class action_plugin_ismsaddons extends ActionPlugin
{
    public function __construct() {

	$this->triples =& plugin_load('helper', 'strata_triples');

	}	
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, 'updateRiskData',null,100);
        $controller->register_hook('STRATA_PREVIEW_METADATA_RENDER', 'AFTER', $this, 'updateRiskData',null,100);
    }

	function getProperty($item,$predicate)
	{		
		$properties = $this->triples->fetchTriples ($item,$predicate,null,null);
		if ($properties)
			return ($properties[0]["object"]);
		else {
			syslog(LOG_INFO, "Missing property ".$predicate." for ".$item);	
			return null;
		}
	}

	function changeProperty($item,$predicate,$value)
	{
		if (is_null($predicate)) {
			syslog(LOG_INFO, "Missing predicate");
			return;
		}

		if ($this->getProperty($item,$predicate) != $value) {
			$this->triples->removeTriples($item,$predicate,null,null);
			$this->triples->addTriple($item, $predicate,$value,$item);
		}
	}

	function checkMes($scnID,$meslabel,$tmes,&$deadline)
	{
		$res = true;
		$deadline = "";
                $lmes = $this->triples->fetchTriples($scnID,$this->getLang($meslabel),null,null);
		foreach ($lmes as $emes) {

			$ames = $tmes[$emes['object']];
			$this->triples->addTriple($scnID,$this->getLang("measures"),$emes['object'],$scnID);
			if ($ames['status'] == 'E') continue;
			$res = false;
			if ($ames['status'] == 'P') {
				if ($ames['deadline']> $deadline) {
					$deadline = $ames['deadline'];
				}
			}
			else $deadline = "XXXX";
               		}

		return $res;
	}

    public function updateRiskData(Event $event, $param)
    {
	$ID = $event->data['page'];
	$type = $this->getProperty($ID,"is a");

	if (!in_array($type,array('mes','scn','risk'),true)) return;

	$lrisk = $this->triples->fetchTriples(null,"is a","risk",null);
	$lscn = $this->triples->fetchTriples(null,"is a","scn",null);
	$lmes = $this->triples->fetchTriples(null,"is a","mes",null);

// Assign risk classes to risk levels
        $rlevels = [];
       	$rltriples = $this->triples->fetchTriples($this->getConf('param')."#".$this->getLang('RiskLevel'),null,null,null);
        foreach ($rltriples as $rltriple)
       	{
             if ($rltriple['predicate']!=$this->triples->getConf('title_key')) {
       	        $rlevels[$rltriple['predicate']]=$rltriple['object'];
            }
        }
         asort($rlevels,SORT_NUMERIC);

         $i = 1;
         $this->rlabel[0]="Unknown";
         foreach ($rlevels as $label=> $value)
         {
            for (;$i<=$value;$i++) $this->rlabel[$i]=$label;
         }


		$tmes = [];
		foreach ($lmes as $emes)
		{
			$mesID = $emes['subject'];
			$status = $this->getProperty($mesID,$this->getLang("status"));
			$deadline = ($status == 'P' ? $this->getProperty($mesID,$this->getLang("deadline")):"");
			$tmes[$mesID]= ["status"=>$status,"deadline"=>$deadline];			
		}
		$tscn=[];
		foreach ($lscn as $escn)
		{
			$scnID= $escn['subject'];
			$this->triples->removeTriples($scnID,$this->getLang("measures"),null,null);

			$level = 3;

			if (! $this->checkMes($scnID,"measures3",$tmes,$deadline3)) $level = 2;
                        if (! $this->checkMes($scnID,"measures2",$tmes,$deadline2)) $level = 1;
                        if (! $this->checkMes($scnID,"measures1",$tmes,$deadline1)) $level = 0;
			$this->checkMes($scnID,"measures4",$tmes,$deadline4);

			switch ($level)
			{
				case 1: {
					$VPred = "fl1"; $VFPred = "fl2"; $deadline = $deadline2;
					break;
					}
				case 2: {
					$VPred = "fl2"; $VFPred = "fl3"; $deadline = $deadline3;
					break;
					}
				case 3: {
					$VPred = "fl3"; $VFPred = "fl3"; $deadline = "-";
					break;
					}
				case 0:
				default: {
					 $VPred = "il"; $VFPred = "fl1"; $deadline = $deadline1;
					}
			}

			$Va = $this->getProperty($scnID,$this->getLang($VPred)) ?? 0;
			$VFa = $this->getProperty($scnID,$this->getLang($VFPred)) ?? 0;

			$this->changeProperty($scnID,$this->getLang("al"),$Va);
			$this->changeProperty($scnID,$this->getLang("afl"),$VFa);
			$this->changeProperty($scnID,$this->getLang("deadline"),$deadline);
			if ($this->getConf('auto'))
			{
				$this->changeProperty($scnID,$this->getLang("cl"),$Va);
				$this->changeProperty($scnID,$this->getLang("fl"),$VFa);
			}
		}	

		foreach ($lrisk as $erisk)
		{
			$riskID = $erisk['subject'];
			$impact = $this->getProperty($riskID,$this->getLang("impact"));
			$lscn=$this->triples->fetchTriples($riskID,$this->getLang("scenarios"),null,null);
			$VR = $VFR = 0;
			
			foreach ($lscn as $escn)
			{
				$Vc = $this->getProperty($escn['object'],$this->getLang("cl")) ?? 0;
				if ($Vc > $VR) $VR = $Vc;

               $Vf = $this->getProperty($escn['object'],$this->getLang("fl")) ?? 0;
               if ($Vf > $VFR) $VFR = $Vf;
			}

			$this->changeProperty($riskID,$this->getLang("cl"),$VR);
			$this->changeProperty($riskID,$this->getLang("fl"),$VFR);
			$this->changeProperty($riskID,$this->getLang("RiskLevel"),$impact*$VR);
            $this->changeProperty($riskID,$this->getLang("RiskClass"),$this->rlabel[$impact*$VR]);
		} 		
    }
}
