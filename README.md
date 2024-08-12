# ismsaddons
 Utilities for a Strata based ISMS documentation

This plugin implements additional macros to support a risk management tool based upon Strata.

- page data classes risk, scn, mes correspond to risks, scenarios and mitigation measures.
- page param (configurable) contains data fragments:
  * param#gravite for the risk impact
  * param#vraisemblance for the scenario plausibility
  * param#NiveauRisque for the thresholds of risk levels, computed as risk impact x max (scenarios plausibility)
  * param#CouleurRisque for the color codes corresponding to each risk levels
  * param#Critere for the risk criteria (such as Availabiity, Confidentiality, Integrity)
- within each page with a data class risk
  * property gravité is the risk impact, according to the encoding in param#gravite
  * property scenarios lists all scenarios leading to the current risk
- within each page with a data class scn
  * properties V0, VC and VF represent the initial, current and future plausibilities according to selected measures
  * property Mesures lists all measures that could mitigate the current scenario
- within each page with a data class mes
  * property Statut expresses whether the measure is Effective (E), Planned (P) or Not considered (N)
  
Under these conventions:
- macro \~~RISKTABLE~~ presents a matrix of the risks per impact and plausibility, current and future
- macro \~~RISKINDICATORS~~ presents a table with statistics on the risk level per risk criterion
- macro \~~RISKCHECK~~ presents an alert banner depending upon its current page
  * within a page with class scn, if VC < V0 without any effective measure, or VF < VC without any planned measure
  * within a page with class risk, lists all scenarios leading to the risk with such inconsistencies
  * within any other page, lists all scenarios with such inconsistencies


RECENT CHANGES TO BE FURTHER DOCUMENTED
For each scenario, measures can now be identified as Measures, Measures2 or Measures3, by decreasing effect on the scenario.
The corresponding plausibilities are VF, VF2 and VF3.
An automated Va plausibility is computed and set to VF if all measures in Measures are effective, then VF2 and VF3 where they are complemented with measures from Measures2 and Measures3.

If the auto configuration attribute is set, Vf is copied to Vc.

New attributes are also automatically computed for Risks, Vraisemblance as the maximum plausibility of associated scenarios and NiveauRisque as the product of Vraisemblance and Impact.

When the auto config flag is set, the RISKCHECK macro is disabled.

A full usage example (in French) has been published in repository https://github.com/fkaag71/smsi-example.
