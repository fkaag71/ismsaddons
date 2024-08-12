<?php
/**
 * DokuWiki Plugin ismsaddons (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  François KAAG <francois.kaag@cardynal.fr>
 */

class syntax_plugin_ismsaddons_riskcheck extends \dokuwiki\Extension\SyntaxPlugin
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
        $this->Lexer->addSpecialPattern('~~RISKCHECK~~', $mode, 'plugin_ismsaddons_riskcheck');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $data = array();
        return $data;
    }
	
	function getProperty($item,$predicate)
	{
		$properties = $this->triples->fetchTriples ($item,$predicate,null,null);
		if ($properties)
			return ($properties[0]["object"]);
		else
			return null;
	}
	
	function checkScn ($scn)
	{
		$ErrMsg = "";
		$V0 = $this->getProperty($scn,$this->getLang('il'));
		$VC = $this->getProperty($scn,$this->getLang('cl'));
		$VF = $this->getProperty($scn,$this->getLang('fl'));
			
		if (!$V0) $ErrMsg .= '<p>'.$this->getLang('NoV0').'</p>';
		if (!$VC) $ErrMsg .= '<p>'.$this->getLang('NoVC').'</p>';
		if (!$VF) $ErrMsg .= '<p>'.$this->getLang('NoVF').'</p>';

		if ($ErrMsg !="") return $ErrMsg;
		
		if ($VC > $V0) $ErrMsg .= '<p>'.$this->getLang('HiVC').'</p>';
		if ($VF > $VC) $ErrMsg .= '<p>'.$this->getLang('HiVF').'</p>';
			
		$mesures = $this->triples->fetchTriples($scn,$this->getLang('measures'),null,null);
		$NEff = $NProg = 0;
			
		foreach ($mesures as $mesure)
		{
			$status = $this->getProperty($mesure['object'],$this->getLang('status'));				
			if ($status == $this->getLang('codeEffective')) $NEff ++;
			if ($status == $this->getLang('codePlanned')) $NProg ++;
		}
		if (($VC < $V0) && ($NEff == 0)) $ErrMsg .= '<p>'.$this->getLang('LoVC').'</p>';
		if (($VF < $VC) && ($NProg == 0)) $ErrMsg .= '<p>'.$this->getLang('LoVF').'</p>';		
		return $ErrMsg;
	}
		
    public function render($mode, Doku_Renderer $R, $data) {
	global $ID;
	$scope = GetNS ($ID);
	$page = noNS($ID);

	/* No need to check in auto mode */
	if ($this->getConf('auto')) return true;
	
	if($mode == 'xhtml') {
		$type = $this->getProperty($ID,"is a");
		$R->doc .="<div style='background-color:red;color:white'>";
		
		if ($type == 'scn')
		{
			$R->doc .= $this->checkScn($ID);
		}		
		else if ($type == 'risk')
		{
			$scenarios = $this->triples->fetchTriples ($ID,"Scenarios",null,null);

			foreach ($scenarios as $scn)
			{
				$scnID = $scn['object'];
				$err = $this->checkScn($scnID); 
				if ( $err != "")
				{
					$R->doc .= '<p>'.$this->getLang('ErrSCN').'</p>';
				}
			} 
		}
		else // Page quelconque : tous les scénarios
		{
			$scenarios = $this->triples->fetchTriples (null,"is a","scn",null);

			foreach ($scenarios as $scn)
			{
				$scnID = $scn['subject'];
				$err = $this->checkScn($scnID); 
				if ( $err != "")
				{
					$R->doc .= '<p>'.$this->getLang('ErrSCN').'</p>';
				}
			} 						
		}
		$R->doc .="</div>";
		return true;
   }
	return false;
    }
}
