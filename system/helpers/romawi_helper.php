<?php

function Romawi($n){
$hasil = "";
$iromawi = array("","I","II","III","IV","V","VI","VII","VIII","IX","X",20=>"XX",30=>"XXX",40=>"XL",50=>"L",
60=>"LX",70=>"LXX",80=>"LXXX",90=>"XC",100=>"C",200=>"CC",300=>"CCC",400=>"CD",500=>"D",600=>"DC",700=>"DCC",
800=>"DCCC",900=>"CM",1000=>"M",2000=>"MM",3000=>"MMM");
if(array_key_exists($n,$iromawi)){
$hasil = $iromawi[$n];
}elseif($n >= 11 && $n <= 99){
$i = $n % 10;
$hasil = $iromawi[$n-$i] . Romawi($n % 10);
}elseif($n >= 101 && $n <= 999){
$i = $n % 100;
$hasil = $iromawi[$n-$i] . Romawi($n % 100);
}else{
$i = $n % 1000;
$hasil = $iromawi[$n-$i] . Romawi($n % 1000);
}
return $hasil;
}

?>