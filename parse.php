#!/usr/bin/php
<?php

$file = "";

#help argument
if ($argc==2 && $argv[1]=="--help")
{
echo "Otevreli jste help\n\n",
"Tento program slouzi pro prevod jazyka IPPcode19 do xml\n",
"Kontroluje pouze syntaxi a je mozne spustit jednoduche statistiky\n",
"Spusteni bez argumentu prelozi vstupni kod ze stdin na vystup stdout\n\n",
"dalsi mozne argumenty:\n",
"--stats==file  , kde file je jméno souboru, kam chcete zapisovat statistiky\n",
"--loc    , vypise do souboru file pocet instrukci\n",
"--comments   , vypise do souboru file pocet komentaru\n",
"--jumps    , vypise do souboru file pocet skoku\n",
"--labels    , vypise do souboru file pocet unikatnich navesti\n",
"argumenty se muzou kombinovat jakkoli, --stats by se mel nachazet pouze jednou\n",
"--loc --comments --jumps a --labels se nesmi vyskytovat bez argumentu --stats\n",
"priklady spusteni:\n",
"php parse.php --help <input\n",
"php parse.php <input\n",
"php parse.php --stats==staty --loc --labels --comments --jumps <input\n";
exit(0);
}

else if ($argc!=1)
{
	array_shift($argv);		#oddělení cesty programu
	$i=0;
	foreach ($argv as &$arg) 
	{
		#obsahuje pouze povolené argumenty
		if (preg_match("/(^--loc$)|(^--comments$)|(^--labels$)|(^--jumps$)|(^--stats=\S+$)/", $arg))
		{
			if (preg_match("/^--stats=\S+$/", $arg))
			{
				#osetreni zadani argumentu --stat vicekrat (mozno vicekrat pokud ostatni jsou pouze ve tvaru --stats= bez cesty
				if ($file!=="")
				{
					echo "chyba v argumentech\n";
					exit(10);
				}
				$file = explode("=",$arg,2)[1];
				unset($argv[$i]);	#vymazani stat z argumentu
			}
		}
		else
		{
			echo "chyba v argumentech\n";
			exit(10);
		}
		$i++;
	}
	unset($arg);
}

#pomocné countery pro rozšíření
$linecount = 1;
$commentcount = 0;
$labellist= array();
$jumpcount = 0;



if (strpos(($firstline = fgets(STDIN)),'#') != false)
	$commentcount++;

if (strcasecmp(trim(explode('#',$firstline,2)[0]),".IPPcode19")){
	echo "spatna hlavicka\n";
	exit(21);
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#parametr argcount - počet očekávaných argumentů
#transformace argumentů regulárním výrazem,
#rozdělení argumentů bílými znaky
# return list argumentů bez bílých znaků a správný počet
function handle_args($args,$argcount,$linecount) {
    $args = trim($args);
	
	if ($argcount==1 && preg_match('/^\S+$/',$args)){
		return preg_split('/\s+/', $args);
	} else if ($argcount==2 && preg_match('/^\S+\s+\S+$/',$args)){
		return preg_split('/\s+/', $args);
	} else if ($argcount==3 && preg_match('/^\S+\s+\S+\s+\S+$/',$args)){
		return preg_split('/\s+/', $args);
	} else {
		echo "spatny pocet argumentu! radek:",$linecount,"\n";
		exit(23);
	}
}

#regularni vyraz pro variable
$pattern_var = '/^(LF|TF|GF)@[_$\-&%*!?a-zA-Z][_$\-&%*!?0-9a-zA-Z]*$/';
#regularni vyraz pro variable nebo constant
$pattern_symb = '/(^(LF|TF|GF)@[_$\-&%*!?a-zA-Z][_$\-&%*!?\da-zA-Z]*$)|(^string@([^\s#\\\\]|(\\\\\d{3}))*$)|(^int@[\-+]?\d+$)|(^nil@nil$)|(^bool@(false|true)$)/';
#regularni vyraz pro label
$pattern_label = '/^[_$\-&%*!?a-zA-Z][_$\-&%*!?a-zA-Z\d]*$/';
#regularni vyraz pro type
$pattern_type = '/^(int|nil|string|bool)$/';
#regularni vyraz pro constant
$pattern_const = '/(^string@([^\s#\\\\]|(\\\\\d{3}))*$)|(^int@[\-+]?\d+$)|(^nil@nil$)|(^bool@(false|true)$)/';


#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola že řetězec je prázdný nebo obsahuje maximálně bílé znaky
function handle_0arg($args,$linecount) {
    $args = trim($args);
	if ($args==="")
		return;
	else {
		echo "chyba v argumentech na ",$linecount,". řádku\n";
		exit(23);
	}
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_var($args,$linecount) {
	$arrargs = handle_args($args,1,$linecount);
	if(preg_match($GLOBALS["pattern_var"],$arrargs[0])){
		echo "\t\t<arg1 type=\"var\">",htmlspecialchars($arrargs[0], ENT_QUOTES),"</arg1>\n";
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_label($args,$linecount) {
	$arrargs = handle_args($args,1,$linecount);
	if(preg_match($GLOBALS["pattern_label"],$arrargs[0])){
		echo "\t\t<arg1 type=\"label\">",$arrargs[0],"</arg1>\n";
		return $arrargs[0];
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_symb($args,$linecount) {
	$arrargs = handle_args($args,1,$linecount);
	
	if (preg_match($GLOBALS["pattern_var"],$arrargs[0])){		#pokud var
		echo "\t\t<arg1 type=\"var\">",htmlspecialchars($arrargs[0], ENT_QUOTES),"</arg1>\n";
		
	} else if (preg_match($GLOBALS["pattern_const"],$arrargs[0])){	#jinak pokud const
		echo "\t\t<arg1 type=\"",explode("@", $arrargs[0],2)[0] ,"\">",htmlspecialchars(explode("@", $arrargs[0],2)[1], ENT_QUOTES),"</arg1>\n";
		
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_var_symb($args,$linecount) {
	$arrargs = handle_args($args,2,$linecount);
	if(preg_match($GLOBALS["pattern_var"],$arrargs[0]) && preg_match($GLOBALS["pattern_symb"],$arrargs[1])){
		echo "\t\t<arg1 type=\"var\">",htmlspecialchars($arrargs[0], ENT_QUOTES),"</arg1>\n";
		
		if (preg_match($GLOBALS["pattern_var"],$arrargs[1])){		#pokud var
			echo "\t\t<arg2 type=\"var\">",$arrargs[1],"</arg2>\n";
		
		} else if (preg_match($GLOBALS["pattern_const"],$arrargs[1])){	#jinak pokud const
			echo "\t\t<arg2 type=\"",explode("@", $arrargs[1])[0] ,"\">",htmlspecialchars(explode("@", $arrargs[1],2)[1], ENT_QUOTES),"</arg2>\n";
		}
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_var_type($args,$linecount) {
	$arrargs = handle_args($args,2,$linecount);
	if(preg_match($GLOBALS["pattern_var"],$arrargs[0]) && preg_match($GLOBALS["pattern_type"],$arrargs[1])){
		echo "\t\t<arg1 type=\"var\">",htmlspecialchars($arrargs[0], ENT_QUOTES),"</arg1>\n";
		echo "\t\t<arg2 type=\"type\">",$arrargs[1],"</arg2>\n";
		
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_var_symb_symb($args,$linecount) {
	$arrargs = handle_args($args,3,$linecount);
	if(preg_match($GLOBALS["pattern_var"],$arrargs[0]) && preg_match($GLOBALS["pattern_symb"],$arrargs[1]) && preg_match($GLOBALS["pattern_symb"],$arrargs[2])){
		echo "\t\t<arg1 type=\"var\">",htmlspecialchars($arrargs[0], ENT_QUOTES),"</arg1>\n";
		
		if (preg_match($GLOBALS["pattern_var"],$arrargs[1])){		#pokud var
			echo "\t\t<arg2 type=\"var\">",$arrargs[1],"</arg2>\n";
		
		} else if (preg_match($GLOBALS["pattern_const"],$arrargs[1])){	#jinak pokud const
			echo "\t\t<arg2 type=\"",explode("@", $arrargs[1])[0] ,"\">",htmlspecialchars(explode("@", $arrargs[1],2)[1], ENT_QUOTES),"</arg2>\n";
		}
		
		if (preg_match($GLOBALS["pattern_var"],$arrargs[2])){		#pokud var
			echo "\t\t<arg3 type=\"var\">",$arrargs[2],"</arg3>\n";
		
		} else if (preg_match($GLOBALS["pattern_const"],$arrargs[2])){	#jinak pokud const
			echo "\t\t<arg3 type=\"",explode("@", $arrargs[2])[0] ,"\">",htmlspecialchars(explode("@", $arrargs[2],2)[1], ENT_QUOTES),"</arg3>\n";
		}
		
		
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}	
}

#parametr args, který obsahuje řetězec za OPCODE
#parametr linecount: řádek na kterém se nacházíme (pro lepší error hlášky)
#funkce transformace argumentů funkcí handle_args,
#kontrola pomocí regexu, že sedí pořadí a syntaxe
# výpis xml
function handle_label_symb_symb($args,$linecount) {
	$arrargs = handle_args($args,3,$linecount);
	if(preg_match($GLOBALS["pattern_label"],$arrargs[0]) && preg_match($GLOBALS["pattern_symb"],$arrargs[1]) && preg_match($GLOBALS["pattern_symb"],$arrargs[2])){
		echo "\t\t<arg1 type=\"label\">",$arrargs[0],"</arg1>\n";
					
		if (preg_match($GLOBALS["pattern_var"],$arrargs[1])){		#pokud var
			echo "\t\t<arg2 type=\"var\">",$arrargs[1],"</arg2>\n";
		
		} else if (preg_match($GLOBALS["pattern_const"],$arrargs[1])){	#jinak pokud const
			echo "\t\t<arg2 type=\"",explode("@", $arrargs[1])[0] ,"\">",htmlspecialchars(explode("@", $arrargs[1],2)[1], ENT_QUOTES),"</arg2>\n";
		}
		
		if (preg_match($GLOBALS["pattern_var"],$arrargs[2])){		#pokud var
			echo "\t\t<arg3 type=\"var\">",$arrargs[2],"</arg3>\n";
		
		} else if (preg_match($GLOBALS["pattern_const"],$arrargs[2])){	#jinak pokud const
			echo "\t\t<arg3 type=\"",explode("@", $arrargs[2])[0] ,"\">",htmlspecialchars(explode("@", $arrargs[2],2)[1], ENT_QUOTES),"</arg3>\n";
		}
	
	} else {
		echo "chyba v argumentech na radku ",$linecount,"\n";
		exit(23);
	}	
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<program language=\"IPPcode19\">\n";
$counter = 0;

while (FALSE !== ($line = fgets(STDIN))){
	$linecount++;
	
	if (strpos(($line),'#') != false)
		$commentcount++;

	$line = trim(explode('#',$line,2)[0]);
	$opcode = explode(' ',$line,2)[0];
	$args = explode(' ',$line,2)[1];
	if($opcode=="")
		continue;
	
	echo "\t<instruction order=\"",++$counter,"\" opcode=\"";
	switch (strtoupper($opcode)) {
    case "":
        break;
    case "MOVE":
        echo "MOVE\">\n";
		handle_var_symb($args,$linecount);  //zkontroluje počet argumentů v args (zde 0) a vypíše xml.
        break;
    case "CREATEFRAME":
        echo "CREATEFRAME\">\n";
		handle_0arg($args,$linecount);
        break;
    case "PUSHFRAME":
        echo "PUSHFRAME\">\n";
		handle_0arg($args,$linecount);
        break;
    case "POPFRAME":
        echo "POPFRAME\">\n";
		handle_0arg($args,$linecount);
        break;
    case "DEFVAR":
        echo "DEFVAR\">\n";
		handle_var($args,$linecount);
        break;
    case "CALL":
        echo "CALL\">\n";
		handle_label($args,$linecount);
		break;
    case "RETURN":
        echo "RETURN\">\n";
		handle_0arg($args,$linecount);
        break;
    case "PUSHS":
        echo "PUSHS\">\n";
		handle_symb($args,$linecount);
        break;
    case "POPS":
        echo "POPS\">\n";
		handle_var($args,$linecount);
        break;
    case "ADD":
        echo "ADD\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "SUB":
        echo "SUB\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "MUL":
        echo "MUL\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "IDIV":
        echo "IDIV\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "LT":
        echo "LT\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "GT":
        echo "GT\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "EQ":
        echo "EQ\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "AND":
        echo "AND\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "OR":
        echo "OR\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "NOT":
        echo "NOT\">\n";
		handle_var_symb($args,$linecount);
        break;
    case "INT2CHAR":
        echo "INT2CHAR\">\n";
		handle_var_symb($args,$linecount);
        break;
    case "STRI2INT":
        echo "STRI2INT\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "READ":
        echo "READ\">\n";
		handle_var_type($args,$linecount);
        break;
    case "WRITE":
        echo "WRITE\">\n";
		handle_symb($args,$linecount);
        break;
    case "CONCAT":
        echo "CONCAT\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "STRLEN":
        echo "STRLEN\">\n";
		handle_var_symb($args,$linecount);
        break;
    case "GETCHAR":
        echo "GETCHAR\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "SETCHAR":
        echo "SETCHAR\">\n";
		handle_var_symb_symb($args,$linecount);
        break;
    case "TYPE":
        echo "TYPE\">\n";
		handle_var_symb($args,$linecount);
        break;
    case "LABEL":
        echo "LABEL\">\n";
		$labellist[] = handle_label($args,$linecount);
        break;
    case "JUMP":
        echo "JUMP\">\n";
		$jumpcount++;
		handle_label($args,$linecount);
        break;
    case "JUMPIFEQ":
        echo "JUMPIFEQ\">\n";
		$jumpcount++;
		handle_label_symb_symb($args,$linecount);
        break;
    case "JUMPIFNEQ":
        echo "JUMPIFNEQ\">\n";
		$jumpcount++;
		handle_label_symb_symb($args,$linecount);
        break;
    case "EXIT":
        echo "EXIT\">\n";
		handle_symb($args,$linecount);
        break;
    case "DPRINT":
        echo "DPRINT\">\n";
		handle_symb($args,$linecount);
        break;
    case "BREAK":
        echo "BREAK\">\n";
		handle_0arg($args,$linecount);
        break;	
	default:
		echo "something bad happened, wrong OPCODE\n";
		exit(22);
	}
		echo "\t</instruction>\n";
	
}
echo "</program>\n";

if($file!="")
{
	$searchh = array("--loc","--comments","--labels","--jumps");
	$replacee = array($counter,$commentcount,count(array_unique($labellist)),$jumpcount);

	$staty = implode("\n",$argv);
	$staty = str_replace($searchh, $replacee, $staty);
	
	if (!file_put_contents($file, $staty))
	{
		echo("nepovedl se zápis statistik\n");
		exit(12);
	}
}
?>