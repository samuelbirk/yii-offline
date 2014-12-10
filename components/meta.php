<?php
namespace app\components;
 
 
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
 
class meta extends Component
{
	/********************
	metaHedges

	Hedges meta-analytical approach with correlation coefﬁ-
	cients as effect sizes

	Description

	Implements the Hedges meta-analytical approach to fixed and random effects with correlation coefﬁ-
	cients as effect sizes, as described by Hedges
	Usage



	*********************/
	function Hedges($r, $n, $labels, $level=.95){
		if($level==.95){
			$zscore=1.96;	
		}
		elseif($level==.99){
			$zscore=2.58;	
		}
		$stat['total sample size']=array_sum($n);
		$stat['number of correlations']=count($r);
		$stat['r']=$r;
		$stat['n']=$n;
		$z1=$stat['z1']=$this->convertR2Z1($r);
		$z2=$stat['21']=$this->convertR2Z2($r);
		$w=$stat['w']=$this->createWs($n);
		$sum_w=$stat['sum_w']=array_sum($w);
		$zw=$stat['zw']=$this->createZW($z2,$w);
		$sum_zw=$stat['sum_zw']=array_sum($zw);
		$z_bar=$stat['z_bar']=@($sum_zw/$sum_w);
		$w_z_diff_squared=$stat['w_z_diff_squared']=$this->createZW_Diff($z2,$z_bar,$w);
		$Q=$stat['Q']=array_sum($w_z_diff_squared);
		$w_squared=$stat['w_squared']=$this->squared($w);
		$w_squared_sum=$stat['w_squared_sum']=array_sum($w_squared);
		//=(G8-3)/(518-(75756/518))
		$df=3;
		$revc=$stat['revc']=@(($Q-$df)/($sum_w-($w_squared_sum/$sum_w)));
		$r_fixed=$stat['r_fixed']=tanh($z_bar);
		$z_upper_fixed=$stat['z_upper_fixed']=$z_bar+$zscore*sqrt(1/$sum_w);
		$z_lower_fixed=$stat['z_lower_fixed']=$z_bar-$zscore*sqrt(1/$sum_w);
		$r_upper_fixed=$stat['r_upper_fixed']=tanh($z_upper_fixed);
		$r_lower_fixed=$stat['r_lower_fixed']=tanh($z_lower_fixed);
		
		$w_prime=$stat['w_prime']=$this->getWprime($w,$revc);
		$sum_w_prime=$stat['sum_w_prime']=array_sum($w_prime);
		$zw_prime=$stat['zw_prime']=$this->createZW($z2,$w_prime);
		$sum_zw_prime=$stat['sum_zw_prime']=array_sum($zw_prime);
		$z_random=$stat['z_random']=@($sum_zw_prime/$sum_w_prime);
		//echo $z_random."+".$zscore."*sqrt(1/".$sum_w_prime;
		$weighted_se=@(1/$sum_w_prime);
		$weighted_se=sqrt($weighted_se);
		$weighted_se=$zscore*$weighted_se;
		
		$z_upper_random=$stat['z_upper_random']=$z_random+$weighted_se;
		$z_lower_random=$stat['z_lower_random']=$z_random-$weighted_se;
		$r_random=$stat['r_random']=tanh($z_random);
		$r_upper_random=$stat['r_upper_random']=tanh($z_upper_random);
		$r_lower_random=$stat['r_lower_random']=tanh($z_lower_random);
		
		
		$weighted_se_revc=sqrt($revc);
		$weighted_se_revc=$zscore*$weighted_se_revc;
		$z_upper_cr=$stat['z_upper_random_cr']=$z_random+$weighted_se_revc;
		$z_lower_cr=$stat['z_lower_random_cr']=$z_random-$weighted_se_revc;
		$r_upper_cr=$stat['r_upper_random_cr']=tanh($z_upper_cr);
		$r_lower_cr=$stat['r_lower_random_cr']=tanh($z_lower_cr);
		
		
		return $stat;
		
	}
	function convertR2Z1($r){
		foreach($r as $K =>$V){
			$z1[$K]=atanh($V);	
		}
		return $z1;
	}

	function convertR2Z2($r){
		foreach($r as $K =>$V){
		$z2[$K]=@(0.5*log((1+$V)/(1-$V)));
		}
		return $z2;
	}

	function createWs($n){
		foreach($n as $K =>$V){
		$w[$K]=$V-3;
		}
		return $w;
	}

	function createZW($z,$w){
		foreach($z as $K =>$V){
			$zw[$K]=$V*$w[$K];	
		}
		return $zw;
	}

	function createZW_Diff($z,$z_bar,$w){
		foreach($z as $K =>$V){
		$diff[$K]=$w[$K]*($V-$z_bar)*($V-$z_bar);	
		}
		return $diff;
	}
	function squared($num){
		foreach($num as $K =>$V){
			$num_squared[$K]=$V*$V;	
		}
		return $num_squared;
	}

	function getWprime($w,$revc){
		foreach($w as $K =>$V){
			$w_prime[$K]=@(1/(1/$V+$revc));
		}
		return $w_prime;
	}
}
?>