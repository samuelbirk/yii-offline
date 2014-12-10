<?php
namespace app\components;
 
 
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
 
class convertES extends Component
{
	/*
	Post-test only, with control group:
	(Post-test Mean for treatment group – Post-test Mean for control group)/Pooled SD across groups

	*/
	function PostTestBetweenGroups($TreatmentMean,$TreatmentSD,$TreatmentN,$ControlMean,$ControlSD,$ControlN){
		
		$top=$TreatmentMean-$ControlMean;
		$bottom = $this->PooledSampleSDs($TreatmentSD, $TreatmentN, $ControlSD, $ControlN);
		return @($top/$bottom);
	}

	/*Pooled SD formula (from pg 173 of Lipsey & Wilson):
	 * Sqrt[((treatment N – 1)*treatment SD squared + (control N – 1)*control SD squared)/(treatment N + control N – 2)]
	*/
	function PooledSampleSDs($TreatmentSD, $TreatmentN, $ControlSD, $ControlN){
		$top = (($TreatmentN - 1) * ($TreatmentSD * $TreatmentSD) + ($ControlN - 1) * ($ControlSD * $ControlSD));
		$bottom = ($TreatmentN + $ControlN - 2);
		return @(sqrt($top/$bottom));
	}

	/*
	Single group, pretest-posttest:
	(Post-test Mean for treatment group – pre-test Mean for treatement group)/treatement group pre-test SD 
	*/

	function PreTestPostTestTreatmentD($PreMean,$PreSD,$PostMean,$PostSD){
		if($PostMean!='' && $PreMean!='' & $PreSD!=''){
			$val=	@(($PostMean - $PreMean)/$PreSD);
		}
		else{
			$val='';	
		}
		return  $val;
	}

	/*
	Pretest-Posttest, with control group:
	[(Post-test Mean for treatment group – Pre-test Mean for treatment group)/ treatment group pre-test SD] – [(Post-test Mean for control group – Pre-test Mean for control group)/ control group pre-test SD] 
	*/

	function PretestPosttestWControlD($TreatmentPostTestMean,$TreatmentPreTestMean, $TreatmentPreTestSD,$ControlPreTestMean,$ControlPreTestSD,$ControlPostTestMean){
	return @(($TreatmentPostTestMean- $TreatmentPreTestMean)/ $TreatmentPreTestSD) - (($ControlPostTestMean - $ControlPreTestMean)/ $ControlPreTestSD);
	}

	/*
	Once ds have been converted to rs, they are really point-biserial rs except in the single group design and should be converted to biserial correlations as follows:

	From H&S 2004 formula 7.17 and 7.18 (p. 280):

	r corrected = ar/(sqrt[(a squared – 1)(r squared) + 1]),

	where a = sqrt[(.25/pq)],

	where p = percent in treatment group (i.e., .56), q = percent in control group (i.e., 1-p).
	*/

	function rCorrected($r, $TreatmentN,$ControlN){
	if($r!='' && $TreatmentN!=$ControlN){
		$p=@($TreatmentN/($TreatmentN+$ControlN));
		$q=(float) 1-$p;
		$b=@(.25/($p*$q));
		$a=sqrt($b);

		$top=$a*$r;
		$c=(float) pow($a,2)-1;
		$d=(float) pow($r,2)+1;
		
		$e=(float) $c*$d;
		
	    $bottom=sqrt($e);
		$final=@($top/$bottom);
		
		return $final;
	}
	else{
		return $r;	
	}
		
	}

	/*
	I think this formula is used in the excel spreadsheet that comes with the H&S 2004 book. Robbins used this formula BEFORE combining studies meta-anlytically (thus, it increased the uncorrected mean correlation and the corrected mean correlation).  I would suggest doing the same.  Just make sure we don’t report this corrected correlation as the uncorrected correlation in the appendix.  

	When using this formula, H&S say you’re supposed to correct the sampling error variance of the corrected correlation (see p. 280) or it will overestimate the SD of rho (i.e., the corrected correlation) and your results will be conservative.  However, I’ve never seen it mentioned in a manuscript.  I say we don’t worry about it b/c I’d rather just have more conservative (i.e. overestimated) sampling error variance than deal with the formula.  If you did want to correct the sampling error variance, you’d want to enter the r first and then correct the mean corrected correlation using the formula above and correct the sampling error variance of the corrected correlation with this formula (see p. 280):

	Sampling error variance of the corrected correlation = ((corrected r/uncorrected r)squared)*sampling error variance of the uncorrected correlation
	*/

	function SamplingErrorVarianceOfTheCorrectedCorrelation($correctedR,$uncorrectedR,$SamplingErrorVarianceOfTheUncorrectedCorrelation){
		$term1=pow(($correctedR/$uncorrectedR),2);
		return @($term1*$SamplingErrorVarianceOfTheUncorrectedCorrelation);
	}

	/**
	* From Comprehensive Meta Analysis Chapter 7
	* r = d / sqrt(d^2 +a);
	* where a = (n1+n2)/n1*n2
	**/
	function convertD2R($d, $n1, $n2){
		if($d!=''){
		 	$a = ($n1+$n2)/$n1*$n2;
		 	$r = @($d/sqrt(pow($d,2)+$a));
		}
		return $r;
	}

	function convertEffectSize($From, $ESInfo){
		extract($ESInfo);
	if($From=='t-statistic'){
		$df=$N-1;
		$top=pow($ES,2);
		$bottom=pow($ES,2)+$df;
		$w=@($top/$bottom);
		$data['r']=sqrt($w);
		$data['d']=@((2*$ES)/sqrt($df));
	}
	elseif($From=='z-statistic'){
		$top=pow($ES,2);
		$bottom=pow($ES,2)+$N;
		$w=@($top/$bottom);
		$data['r']=sqrt($w);
		$data['d']=@((2*$ES)/sqrt($N));
	}
	elseif($From=='F-statistic'){
		if($dfn==1){
			$w=@($ES/($ES+$dfd));
			$data['r']=sqrt($w);
			$d=@($ES/$dfd);
			$data['d']=2*sqrt($d);
		}
		else{
			$w=@(($ES*$dfn)/($ES+$dfd));
			$data['r']=sqrt($w);
			$d=@(($dfn*$ES)/$dfd);
			$data['d']=2*sqrt($d);	
		}
	}
	elseif($From=='chisquared' || $From=='xsquared'){
		if($dfn==1){
			$w=@($ES/$N);
			$data['r']=sqrt($w);
			$d=@($ES/($N-$ES));
			$data['d']=2*sqrt($d);
		}
		else{
			$w=@($ES/($ES+$N));
			$data['r']=sqrt($w);
			$d=@($ES/($N));
			$data['d']=2*sqrt($d);	
		}
		

	}
	elseif($From=='Correlation' || $From=='r'){
		$data['r']=$ES;
		$top=4*pow($ES,2);
		$bottom=1-pow($ES,2);
		$w=@($top/$bottom);
		$data['d']=sqrt($w);
	}
	elseif($From=='d-statistic' || $From=='d'){
		convertD2R($ES);
		$data['r']=convertD2R($ES);
		$data['d']=$ES;
	}
	return $data;
	}




	/*REFERENCES
	Borenstein (2009). Effect sizes for continuous data. In H. Cooper, L. V. Hedges, & J. C. Valentine (Eds.), The handbook of research synthesis and meta analysis (pp. 279-293). New York: Russell Sage Foundatio$n

	Cooper, H., Hedges, L.V., & Valentine, J.C. (2009). The handbook of research synthesis and metaanalysis (2nd edition). New York: Russell Sage Foundatio$n*/
	function d2ES($d, $n1, $n2=NULL) {

		if($n2==NULL){
			$n1=$n1/2;
			$n2=$n1;	
		}
	    $out['d']=$d;
        $out['d.var'] = $dvar = ($n1+$n2)/($n1*$n2)+ (pow($d,2))/(2*($n1+$n2));
        $df = ($n1+$n2)-2;
        $j = 1-(3/(4*$df-1));
        $out['g'] = $g = $j*$d;
        $out['g.var'] = $gvar = pow($j,2)*$dvar;
        $a = (pow(($n1 + $n2),2))/($n1*$n2); // will correct for inbalanced n, if applicable
        $n = $n1 + $n2;
        $out['r'] = $r = $d/sqrt((pow($d,2)) + $a); // to compute r from d
        $out['r.var'] = $rvar = (pow($a,2)*$dvar)/pow((pow($d,2) + $a),3);
        $out['lor'] = $lor = $d*(pi()/sqrt(3));
        $out['lor.var'] = $lorvar = $dvar*(pow(pi(),2)/3);
        $out['z'] = $z =  0.5*log((1 + $r)/(1-$r)) ; 
        $out['z.var'] = $zvar = 1/($n-3); 
        $out['zscore'] = $zscore = $r/sqrt($rvar);
        return $out;

	}



	# Formulas for computing effect sizes (d, r, log odds ratio)
	# in designs with independent groups.
	# Computing effect sizes d and g, independent groups
	# (0) Study reported: 
	# m.1 (post-test mean of treatment), m.2 (post-test mean of comparison),
	# sd.1 (treatment standard deviation at post-test), sd.2 (comparison 
	# standard deviation at post-test), $n1 (treatment), $n2 (comparison/control).
	function PostTestComparison($m1,$m2,$sd1,$sd2,$n1, $n2=NULL) {
		if($n2==NULL){
			$n1=$n1/2;
			$n2=$n1;	
		}
	  	$s_within=sqrt((($n1-1)*$sd1^2+($n2-1)*$sd2^2)/($n1+$n2-2));
	  	$out['d'] = $d=($m1-$m2)/$s_within;
	  	$out['d.var'] = $dvar = ($n1+$n2)/($n1*$n2)+ (pow($d,2))/(2*($n1+$n2));
	  	$df= ($n1+$n2)-2;
	  	$j=1-(3/(4*$df-1));
	  	$out['g'] = $g=$j*$d;
	  	$out['g.var'] = $gvar=pow($j,2)*$dvar;
	  	$a = (pow(($n1 + $n2),2))/($n1*$n2);  # will correct for inbalanced n, if applicable
	  	$out['r'] = $r = $d/sqrt((pow($d,2)) + $a);
	  	$out['r.var'] = $rvar = (pow($a,2)*$dvar)/pow((pow($d,2) + $a),3);
	  	$out['lor'] = $lor = pi()*$d/sqrt(3);
	  	$out['lor.var'] = $lorvar = pow(pi(),2)*$dvar/3;
	  	$n = $n1 + $n2;
	  	$out['z'] = $z =  0.5*log((1 + $r)/(1-$r));  
		#computing r to z for each study
	  	$out['z.var'] = $zvar = 1/($n-3); 
	  	$out['zscore'] = $zscore = $r/sqrt($rvar);
	  	return $out;

	}


	# (2) Study reported: 
	# t (t-test value of treatment v comparison), $n1 (treatment),
	# $n2 (comparison/control).



	function t2ES($t, $n1, $n2=NULL) { #If only total n reported just split .5
		if($n2==NULL){
			$n1=$n1/2;
			$n2=$n1;	
		}
	  	$out['d']=$d=$t*sqrt(($n1+$n2)/($n1*$n2));
	  	$out['d.var'] =$dvar=($n1+$n2)/($n1*$n2)+ (pow($d,2))/(2*($n1+$n2));
	  	$df= ($n1+$n2)-2;
	  	$j=1-(3/(4*$df-1));
	  	$out['g'] = $g=$j*$d;
	  	$out['g.var']=$gvar=pow($j,2)*$dvar;
	  	$n = $n1 + $n2;
	  	$out['r'] = $r = sqrt((pow($t,2))/(pow($t,2) + $n-2));
	  	$out['r.var'] = $rvar = (pow((1-pow($r,2)),2))/($n-1);
	  	$out['lor']=$lor = pi()*$d/sqrt(3);
	  	$out['lor.var'] = $lorvar = pow(pi(),2)*$dvar/3;
	  	$out['z'] = $z =  0.5*log((1 + $r)/(1-$r));  
	  	$out['z.var'] = $zvar = 1/($n-3); 
	  	return $out;
	}




	function F2ES($f,$n1, $n2=NULL) {
		if($n2==NULL){
			$n1=$n1/2;
			$n2=$n1;	
		}
		$out['d']=$d=sqrt($f*($n1+$n2)/($n1*$n2));
		$out['d.var'] =$dvar=($n1+$n2)/($n1*$n2)+ (pow($d,2))/(2*($n1+$n2));
		$df= ($n1+$n2)-2;
		$j=1-(3/(4*$df-1));
		$out['g'] = $g=$j*$d;
		$out['g.var'] = $g=pow($j,2)*$dvar;
		$a = (pow(($n1 + $n2),2))/($n1*$n2);  # will correct for inbalanced n, if applicable
		$out['t'] = $t = sqrt($f);
		$n = $n1 + $n2;
		#r= sqrt((t^2)/(t^2 + n-2)) # to compute r from f with 1 df
		$out['r'] = $r = $d/sqrt((pow($d,2)) + $a); # to compute r from d
		$out['r.var'] = $rvar = (pow($a,2)*$dvar)/pow((pow($d,2) + $a),3);
		$out['lor'] = $lor = pi()*$d/sqrt(3);
		$out['lor.var'] =$lorvar = pow(pi(),2)*$dvar/3;
		$out['z'] = $z =  0.5*log((1 + $r)/(1-$r));  
		$out['z.var'] = $zvar = 1/($n-3); 
		$out['zscore'] = $zscore = $r/sqrt($rvar);
		return $out;
	}



	function p2ES($p, $n1, $n2 = NULL, $tail = "two") {
		if($n2==NULL){
			$n1=$n1/2;
			$n2=$n1;	
		}
	    $n = $n1 + $n2;
	    $df= ($n1+$n2)-2;
	    $j=1-(3/(4*$df-1));
	    $a = (($n1 + $n2)^2)/($n1*$n2); 
	    $out=array();
	    if($tail == "one") {
			$pxtwo=$p*2;
			$TINV=qt((1-$pxtwo/2),$df);
			$out['d'] = $d=$TINV*sqrt(($n1+$n2)/($n1*$n2));
			$out['d.var'] =$dvar=($n1+$n2)/($n1*$n2)+ (pow($d,2))/(2*($n1+$n2));
			$out['g'] = $g=$j*$d;
			$out['g.var'] =$gvar=pow($j,2)*$dvar;
			$out['r']= $r = $d/sqrt((pow($d,2)) + $a); # to compute r from d
			$out['r.var'] = $rvar = (pow($a,2)*$dvar)/pow(pow($d,2 + $a),3);
			$out['lor'] = $lor = pi()*$d/sqrt(3);
			$out['lor.var'] = $lor = pow(pi(),2)*$dvar/3;
			$out['z'] = $z =  0.5*log((1 + $r)/(1-$r));  
			$out['z.var'] = $zvar = 1/($n-3); 
	    }

	    if(tail == "two") {
	        $TINV=qt((1-$p/2),$df);
	        $out['d'] = $d=$TINV*sqrt(($n1+$n2)/($n1*$n2));
	        $out['dvar']= $dvar=($n1+$n2)/($n1*$n2)+ (pow($d,2))/(2*($n1+$n2));
	        $out['g'] = $g=$j*$d;
	        $out['g.var'] =$gvar=pow($j,2)*$dvar;
	        $out['r']= $r = $d/sqrt((pow($d,2)) + $a); # to compute r from d
	        $out['r.var'] = $rvar = (pow($a,2)*$dvar)/pow(pow($d,2 + $a),3);
	        $out['lor'] = $lor = pi()*$d/sqrt(3);
	        $out['lor.var'] = $lor = pow(pi(),2)*$dvar/3;
	        $out['z'] = $z =  0.5*log((1 + $r)/(1-$r));  
	        $out['z.var'] = $zvar = 1/($n-3); 
	    }
	  return $out;
	}

	function r2ES($r,  $n ) { # If var.r not reported use n
	    $rvar =pow((1-pow($r,2)),2)/($n-1) ;
		$out['d'] = $d = (2*$r)/(sqrt(1-pow($r,2)));
	    $out['d.var'] = $dvar = 4*$rvar/(1-pow($r,2))^3;
		$df = ($n)-2;
	 	$j = 1-(3/(4*$df-1));
	 	$out['g'] = $g = $j*$d;
	  	$out['g.var'] = $gvar = pow($j,2)*$dvar;
	    $out['lor'] = $lor = pi()*$d/sqrt(3);
	    $out['lor.var'] = $lorvar = pow(pi(),2)*$dvar/3;
	    $out['z'] = $z =  0.5*log((1 + $r)/(1-$r));  
	    $out['z.var'] = $zvar = 1/($n-3); 
	    return $out;
	}  

	# computing es from log odds ratio
	function lor2ES($lor, $lorvar=1, $n1, $n2=NULL) { # $n1 =  tmt grp
		if($n2==NULL){
			$n1=$n1/2;
			$n2=$n1;	
		}
	  	$out['d'] =  $d = $lor*sqrt(3)/pi();
	  	$out['dvar'] = $dvar = 3*$lorvar/pow(pi(),2);
	  	$df= ($n1+$n2)-2; 
	  	$j=1-(3/(4*$df-1));
	  	$out['g'] =$g=$j*$d;
	  	$out['g.var']=$gvar=pow($j,2)*$dvar;
	  	$a = (($n1 + $n2)^2)/($n1*$n2);  # will correct for inbalanced n, if applicable
	  	$out['r']=$r = $d/sqrt((pow($d,2)) + $a);
	  	$out['r.var']=$rvar = (pow($a,2)*$dvar)/pow((pow($d,2) + $a),3);
	  	$out['lor']=$lor = pi()*$d/sqrt(3);
	  	$out['lor.var']=$lorvar = pi()^2*$dvar/3;
	  	$n = $n1 + $n2;
	  	$out['z']=$z =  0.5*log((1 + $r)/(1-$r));  #computing r to z for each study
	  	$out['z.var']=$zvar = 1/(n-3) ;
	  	#z.score = r/sqrt(var.r)
	 	return $out;
	}  

	# compute or from proportions
	function prop2ES($p1, $p2, $nab, $ncd) {
	    $or =($p1*(1-$p2))/($p2*(1-$p1));
	    $out['lor'] = $lor = log($or);
	    $out['lor.var']=$lorvar = 1/($nab*$p1*(1-$p1))+1/($ncd*$p2*(1-$p2));
	    $out['d']=$d = $lor*sqrt(3)/pi();
	    $out['d.var']=$dvar = 3*$lorvar/pow(pi(),2);
	    $df= ($nab+$ncd)-2; 
	    $j=1-(3/(4*$df-1));
	    $out['g']=$g=$j*$d;
	    $out['g.var']=$gvar=pow($j,2)*$dvar;
	    $a = (($nab + $ncd)^2)/($nab*$ncd);  # not sure if this is appropriate*
	    $out['r']=$r = $d/sqrt((pow($d,2)) + $a);
	    $out['r.var']=$rvar = (pow($a,2)*$dvar)/pow((pow($d,2) + $a),3);
	    $out['lor']=$lor = pi()*$d/sqrt(3);
	    $out['lor.var']=$lorvar = pow(pi(),2)*$dvar/3;
	    $n = $nab+$ncd;
	    $out['z']=$z =  0.5*log((1 + $r)/(1-$r));  #computing r to z for each study
	    $out['z.var']=$zvar = 1/($n-3); 
	    #z.score = r/sqrt(var.r)
	    return $out;
	}

	# Odds Ratio to es: if have info for failure in both conditions 
	# (B = # tmt failure; D = # non-tmt failure) and the sample size
	# for each group ($n1 & $n0 respectively):
	function failes2ES($B, $D, $n1, $n0) {
		$A = $n1 - $B;  # tmt success
		$B = $B;        # tmt failure
		$C = $n0 - $D;  # non-tmt success
		$D = $D;        # non-tmt failure
		$p1 = $A/$n1;   # proportion 1 
		$p2 = $C/$n0;   # proportion 2
		$nab =  $A+$B;  # n of A+B
		$ncd =  $C+$D;  # n of C+D        
		$or = ($p1 * (1 - $p2))/($p2 * (1 - $p1));  # odds ratio
		$out['lor']=$lor = log($or);  # log odds ratio
		$out['lor.var']=$lorvar =  1/$A + 1/$B + 1/$C + 1/$D;  # variance of log odds ratio
		#var.lor = 1/($nab*p1*(1-p1))+1/($ncd*p2*(1-p2))
		$out['d']=$d = $lor * sqrt(3)/pi();  # conversion to d
		$out['d.var']=$dvar = 3 * $lorvar/pow(pi(),2);  # variance of d
		$df= ($n1 + $n0)-2; 
		$j=1-(3/(4*$df-1));
		$out['g']=$g=$j*$d;
		$out['g.var']=$gvar=pow($j,2)*$dvar;
		$a = (pow(($n1 + $n0),2))/($n1*$n0);  # not sure if this is appropriate*
		$out['r']=$r = $d/sqrt((pow($d,2)) + $a);
		$out['r.var']=$rvar = (pow($a,2)*$dvar)/pow((pow($d,2) + $a),3);
		$out['lor']=$lor = pi()*$d/sqrt(3);
		$out['lor.var']=$lorvar = pow(pi(),2)*$dvar/3;
		$n = $n1 + $n0;
		$out['z']=$z =  0.5*log((1 + $r)/(1-$r));  #computing r to z for each study
		$out['z.var']=$zvar = 1/($n-3); 
		#z.score = r/sqrt(var.r)
		return $out;
	}	

	# Converting Chi-squared statistic with 1 df to es
	function chi2ES($chisq,  $n) {
		$out['r']=$r = sqrt($chisq/$n);
		$out['r.var']=$rvar =pow((1-pow($r,2)),2)/($n-1); 
		$out['d']=$d=2*$r*sqrt(($n-1)/($n*(1-pow($r,2))))*abs($r)/$r; 
		$out['d.var']=$dvar = 4*$rvar/pow((1-pow($r,2)),3);
		$df= ($n)-2 ;
		$j=1-(3/(4*$df-1));
		$out['g']=$g=$j*$d;
		$out['g.var']=$gvar=pow($j,2)*$dvar;
		$out['lor']=$lor = pi()*$d/sqrt(3);
		$out['lor.var']=$lorvar = pow(pi(),2)*$dvar/3;
		$out['z']=$z =  0.5*log((1 + $r)/(1-$r));  
		$out['z.var']=$zvar = 1/($n-3); 
		return $out;
	}

	function convertESFromTo($From, $To, $ES, $ESInfo){
		if($ESInfo['n2']==NULL || $ESInfo['n2']==''){
			$ESInfo['n2']=NULL;
		}
		if($ESInfo['tail']==NULL || $ESInfo['tail']==''){
			$ESInfo['tail']='two';
		}
		if($ESInfo['var']==NULL || $ESInfo['var']==''){
			$ESInfo['var']=NULL;
		}
		extract($ESInfo);
		if(strtolower($From) == 'correlation' || strtolower($From)=='r'){
			$output=	r2ES($ES,  $n );
			
		}
		elseif(strtolower($From)=='d' || strtolower($From)=='d-statistic' || strtolower($From)=='dstat' || strtolower($From)=='dstatistic'){

		$output = d2ES($ES, $n1, $n2) ;	
		}
		elseif(strtolower($From)=='t' || strtolower($From)=='t-statistic' || strtolower($From)=='tstat' || strtolower($From)=='tstatistic'){
		
		$output = t2ES($ES, $n1, $n2);
		}
		elseif(strtolower($From)=='f' || strtolower($From)=='f-statistic' || strtolower($From)=='fstat' || strtolower($From)=='fstatistic'){
		
		$output = F2ES($ES, $n1, $n2);
		}
		elseif(strtolower($From)=='p' || strtolower($From)=='p-value' || strtolower($From)=='probability' ){
		
		$output =p2ES($ES, $n1, $n2, $tail );
		}
		elseif(strtolower($From)=='lor' || strtolower(str_replace(" ","",$From))=='logodds' || strtolower(str_replace(" ","",$From))=='logoddsratio' ){
		
		$output =lor2ES($ES, $var, $n1, $n2);
		}
		elseif(strtolower($From)=='proportion' || strtolower(str_replace(" ","",$From))=='prop' ){
		
		$output =prop2ES($p1, $p2, $nab, $ncd) ;
		}
		elseif(strtolower($From)=='failures' || strtolower(str_replace(" ","",$From))=='success'  || strtolower(str_replace(" ","",$From))=='fails' ){
		
		$output =failes2ES($ES, $D, $n1, $n0) ;
		}
		////SET OUTPUT
		if(strtolower(str_replace(" ","",$To))=='d' || strtolower(str_replace(" ","",$To))=='d-statistic' || strtolower(str_replace(" ","",$To))=='dstat' || strtolower(str_replace(" ","",$To))=='dstatistic'){
			$new_ES=$output['d'];
		}
		elseif(strtolower(str_replace(" ","",$To))=='g' || strtolower(str_replace(" ","",$To))=='g-statistic' || strtolower(str_replace(" ","",$To))=='gstat' || strtolower(str_replace(" ","",$To))=='gstatistic'){
			$new_ES=$output['g'];
		}
		elseif(strtolower(str_replace(" ","",$To))=='r' || strtolower(str_replace(" ","",$To))=='correlation' ){
			$new_ES=$output['r'];
		}
		elseif(strtolower(str_replace(" ","",$To))=='lor' || strtolower(str_replace(" ","",$To))=='logodds' || strtolower(str_replace(" ","",$To))=='logoddsratio' ){
			$new_ES=$output['lor'];
		}
			elseif(strtolower(str_replace(" ","",$To))=='z' || strtolower(str_replace(" ","",$To))=='zstat' || strtolower(str_replace(" ","",$To))=='z-statistic' ){
			$new_ES=$output['z'];
		}
		
		return $new_ES;
	}

	function Norm_p($z) {
	// Returns the two-tailed standard normal probability of z
	    $z = abs($z);
	    $a1 = 0.0000053830;
		$a2 = 0.0000488906; 
		$a3 = 0.0000380036;
	    $a4 = 0.0032776263; 
		$a5 = 0.0211410061;
		$a6 = 0.0498673470;
	    $p = ((((($a1*$z+$a2)*$z+$a3)*$z+$a4)*$z+$a5)*$z+$a6)*z+1;
	    $p = $pow($p, -16);
	    return $p;
	}
	 
	function Norm_z($p) {
	// Returns z given a half-middle tail type p.
	 
	    $a0= 2.5066282;  $a1=-18.6150006;  $a2= 41.3911977;   $a3=-25.4410605;
	        $b1=-8.4735109;  $b2= 23.0833674;  $b3=-21.0622410;   $b4=  3.1308291;
	        $c0=-2.7871893;  $c1= -2.2979648;  $c2=  4.8501413;   $c3=  2.3212128;
	        $d1= 3.5438892;  $d2=  1.6370678; 
			$r; $z;
	 
	    if ($p>0.42) {
	        $r=sqrt(-log(0.5-$p));
	        $z=((($c3*$r+$c2)*$r+$c1)*$r+$c0)/(($d2*$r+$d1)*$r+1);
	    }
	    else {
	        $r=$p*$p;
	        $z=$p*((($a3*$r+$a2)*$r+$a1)*$r+$a0)/(((($b4*r+$b3)*$r+$b2)*$r+$b1)*$r+1);
	    }
	    return $z;
	}
	 


	function RoundDP($x, $dp) {
	// Rounds x to dp decimal places.
	     $powten = pow(10, $dp);
	    return (round($x*$powten)/$powten);
	}

	//--------------END OF COMMON FUNCTIONS----------------------------------------

	function T_p($t, $df) {
		// Returns two-tail probability level given t and df.
	    
	    $abst = abs($t);
		$tsq = $t*$t;
		$p;
		if($df == 1) {
			$p = 1 - 2*atan($abst)/pi();
		}
		elseif($df == 2){ 
			$p = 1 - $abst/sqrt($tsq + 2);
		}
		elseif($df == 3){ 
			$p = 1 - 2*(atan($abst/sqrt(3)) + $abst*sqrt(3)/($tsq + 3))/pi();
		}
		elseif($df == 4){ 
			$p = 1 - $abst*(1 + 2/($tsq + 4))/sqrt($tsq + 4);
		}

		// finds the z equivalent of t and df st they yield same probs.
		$z = T_z($abst, $df);	 
		if($df>4){
			$p = Norm_p($z);
		}
		else{
			$p = Norm_p(z);
		} // small non-integer df
	    return $p;
	}


	function T_z($t, $df) {   
		// Converts a t value to an approximate z value w.r.t the given df
		// s.t. std.norm.(z) = t(z, df) at the two-tail probability level.
	    $A9 = $df - 0.5; 
	    $B9 = 48*$A9*9;
	    $T9 = $t*$t/$df;

	    if($T9 >= 0.04){ 
	    	$Z8 = $A9*log(1+$T9);
	    }
		else { 
			$Z8 = $A9*(((1 - $T9*0.75)*$T9/3 - 0.5)*$T9 + 1)*$T9;
		}

	    $P7 = ((0.4*$Z8 + 3.3)*$Z8 + 24)*$Z8 + 85.5;
	    $B7 = 0.8*pow($Z8, 2) + 100 + $B9;
	    $z = (1 + (-$P7/$B7 + $Z8 + 3)/$B9)*sqrt($Z8);
			
		return $z;
	  
	}


	function qt($p, $df) {
		// Hill's approx. inverse t-dist.: Comm. of A.C.M Vol.13 No.10 1970 pg 620.
		// Calculates t given df and two-tail probability.
	    if($df == 1){ 
	    	$t = cos($p*pi()/2)/sin($p*pi()/2);
	    }
		elseif($df == 2){ 
			$t = sqrt(2/($p*(2 - $p)) - 2);
		}
	    else {
		    $a = 1/($df - 0.5);
		    $b = 48/($a*$a);
		    $c = ((20700*$a/$b - 98)*$a - 16)*$a + 96.36;
		    $d = ((94.5/($b + $c) - 3)/$b + 1)*sqrt($a*pi()*0.5)*$df;
		    $x = $d*$p;
		    $y = pow($x, 2/$df);
		    if($y > 0.05 + $a) {
		        $x = Norm_z(0.5*(1 - $p)); 
		    }
			$y = $x*$x;
			if($df < 5){
				$c = $c + 0.3*($df - 4.5)*($x + 0.6);
			
				$c = (((0.05*$d*$x - 5)*$x - 7)*$x - 2)*$x + $b + $c;
				$y = (((((0.4*$y + 6.3)*$y + 36)*$y + 94.5)/$c - $y - 3)/$b + 1)*$x;
				$y = $a*$y*$y;
				if($y > 0.002){ 
					$y = exp($y) - 1;
				}
			  	else{ 
			  		$y = 0.5*$y*$y + $y;
			  	}
			    $t = sqrt($df*$y);
			}            
		    else {
			$y = ((1/((($df + 6)/($df*$y) - 0.089*$d - 0.822)*($df + 2)*3) 
			    + 0.5/($df + 4))*$y - 1)*($df + 1)/($df + 2) + 1/$y;
		        $t = sqrt($df*$y);
	        }
		}
	    
	    return $t;
	}
 
}
?>