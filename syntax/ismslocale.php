<?php
/**
 * DokuWiki Plugin ismsaddons (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  François KAAG <francois.kaag@cardynal.fr>
 */

class ISMSLocale {
  public $vlabel = array 
         ('fr'=> array(
            'measures' => 'mesures',
            'scenarios' => 'scenarios',
            'status' => 'statut',
            'impact' => 'gravité',
            'criterion' => 'Critère',
            'likelihood' => 'vraisemblance',
            'RiskLevel' => 'NiveauRisque',
            'RiskColor' => 'CouleurRisque',
            'future'=> 'Niveaux courants',
            'present' => 'Niveaux futurs',
            'il' => 'v0',
            'cl' => 'vc',
            'fl' => 'vf',
            'codeEffective' => 'E',
            'codePlanned' => 'P',
            'NoV0' => 'V0 non définie',
            'NoVC' => 'VC non définie',
            'NoVF' => 'VF non définie',
            'HiVC' => 'VC est plus élevée que V0',
            'HiVF' => 'VF est plus élevée que VC',
            'LoVC' => 'VC baisse sans mesures effectives',
            'LoVF' => 'VF baisse sans mesures programmées',
            'ErrScn' => 'Incohérence sur le scénario ',
            'NRiskLabel' => 'Nombre de risques',
            'MaxCLabel'  => 'Niveau max courant',
            'MeanCLabel' => 'Niveau moyen courant',
            'QMeanCLabel' => 'Niveau quadratique courant',
            'MaxFLabel'  => 'Niveau max futur',
            'MeanFLabel' => 'Niveau moyen futur',
            'QMeanFLabel' => 'Niveau quadratique futur'
          ),
          'en'=> array(
            'measures' => 'measures',
            'scenarios' => 'scenarios',
            'status' => 'status',
            'impact' => 'Impact',
            'criterion' => 'Criterion',
            'likelihood' => 'likelihood',
            'RiskLevel' => 'RiskLevel',
            'RiskColor' => 'RiskColor',
            'future'=> 'Future levels',
            'present' => 'Current levels',
            'il' => 'il',
            'cl' => 'cl',
            'fl' => 'fl',
            'codeEffective' => 'E',
            'codePlanned' => 'P',
            'NoV0' => 'IL is not defined',
            'NoVC' => 'CL is not defined',
            'NoVF' => 'FL is not defined',
            'HiVC' => 'CL is higher than IL',
            'HiVF' => 'FL is higher than CL',
            'LoVC' => 'CL is lower than IL without effective measures',
            'LoVF' => 'FL is lower than CL without programmed measures',
            'ErrScn' => 'Inconsistency on scenario ',
            'NRiskLabel' => 'Number of risks',
            'MaxCLabel'  => 'Max current risk level',
            'MeanCLabel' => 'Mean current risk level',
            'QMeanCLabel' => 'Quadratic current risk level',
            'MaxFLabel'  => 'Max future risk level',
            'MeanFLabel' => 'Mean future risk level',
            'QMeanFLabel' => 'Quadratic future risk level'

          )
);
}


