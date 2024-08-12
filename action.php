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
    }

	function getProperty($item,$predicate)
	{		
		$properties = $this->triples->fetchTriples ($item,$predicate,null,null);
		if ($properties)
			return ($properties[0]["object"]);
		else
			return null;
	}

	function changeProperty($item,$predicate,$value)
	{
		$this->triples->removeTriples($item,$predicate,null,null);
		$this->triples->addTriple($item, $predicate,$value,$item);		
	}
	
    /**
     * Event handler for COMMON_WIKIPAGE_SAVE
     *
     * @see https://www.dokuwiki.org/devel:events:COMMON_WIKIPAGE_SAVE
     * @param Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function updateRiskData(Event $event, $param)
    {
		$ID = $event->data['page'];
		$type = $this->getProperty($ID,"is a");
		
		if (!in_array($type,array('mes','scn','risk'),true)) return;

		$lrisk = $this->triples->fetchTriples(null,"is a","risk",null);
		$lscn = $this->triples->fetchTriples(null,"is a","scn",null);
		$lmes = $this->triples->fetchTriples(null,"is a","mes",null);
		
		$tmes = [];
		foreach ($lmes as $emes)
		{
			$mesID = $emes['subject'];
			$status = $this->getProperty($mesID,$this->getLang("status"));
			$deadline = $this->getProperty($mesID,$this->getLang("deadline"));
			$tmes[$mesID]= ["status"=>$status,"deadline"=>$deadline];			
		}
		
		$tscn=[];
		foreach ($lscn as $escn)
		{
			$scnID= $escn['subject'];
			$lmes1 = $this->triples->fetchTriples($scnID,$this->getLang("measures"),null,null);
			$lmes2 = $this->triples->fetchTriples($scnID,$this->getLang("measures2"),null,null);
			$lmes3 = $this->triples->fetchTriples($scnID,$this->getLang("measures3"),null,null);		
			
			$level = 3;
			foreach ($lmes3 as $emes)
			{
				if ($tmes[$emes['object']]['status'] != 'E')
				{ $level = 2; break;}				
			}
			foreach ($lmes2 as $emes)
			{
				if ($tmes[$emes['object']]['status'] != 'E')
				{$level = 1; break;}				
			}
			foreach ($lmes1 as $emes)
			{

				if ($tmes[$emes['object']]['status'] != 'E')
				{$level = 0; break;}				
			}				
			switch ($level)
			{
				case 1: $VPred = "fl"; break;
				case 2: $VPred = "fl2"; break;
				case 3: $VPred = "fl3"; break;
				case 0:
				default: $VPred = "il";
			}	
			$Va = $this->getProperty($scnID,$this->getLang($VPred));				
			$this->changeProperty($scnID,$this->getLang("al"),$Va);
			if ($this->getConf('auto'))
			{
				$this->changeProperty($scnID,$this->getLang("cl"),$Va);
			}
		}	
		
		foreach ($lrisk as $erisk)
		{
			$riskID = $erisk['subject'];
			$impact = $this->getProperty($riskID,$this->getLang("impact"));
			$lscn=$this->triples->fetchTriples($riskID,$this->getLang("scenarios"),null,null);
			$V = 0;
			
			foreach ($lscn as $escn)
			{
				$Vc = $this->getProperty($escn['object'],$this->getLang("cl"));
				if ($Vc > $V) $V = $Vc;
			}			
			$this->changeProperty($riskID,$this->getLang("likelihood"),$V);
			$this->changeProperty($riskID,$this->getLang("RiskLevel"),$impact*$V);
		}		
		
    }
}
