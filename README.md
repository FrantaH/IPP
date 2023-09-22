# IPP
projekt do předmětu IPP 2019

# Analyzátor kódu v IPPcode19 (parse.php)
Skript typu filtr (parse.php v jazyce PHP 7.3) načte ze standardního vstupu zdrojový kód v IPPcode19 (viz sekce 6), zkontroluje lexikální a syntaktickou správnost kódu a vypíše na standardní
výstup XML reprezentaci programu dle specifikace v sekci 3.1.
### Tento skript bude pracovat s těmito parametry:
• --help viz společný parametr všech skriptů v sekci 2.2.
### Chybové návratové kódy specifické pro analyzátor:
• 21 - chybná nebo chybějící hlavička ve zdrojovém kódu zapsaném v IPPcode19;
• 22 - neznámý nebo chybný operační kód ve zdrojovém kódu zapsaném v IPPcode19;
• 23 - jiná lexikální nebo syntaktická chyba zdrojového kódu zapsaného v IPPcode19.
## Popis výstupního XML formátu
Za povinnou XML hlavičkou11 následuje kořenový element program (s povinným textovým atributem
language s hodnotou IPPcode19), který obsahuje pro instrukce elementy instruction. Každý element instruction obsahuje povinný atribut order s pořadím instrukce (počítáno od 1) a povinný
10Relativní cesta nebude obsahovat zástupný symbol ~
(vlnka).
11Tradiční XML hlavička včetně verze a kódování je <?xml version="1.0" encoding="UTF-8"?>
5
atribut opcode (hodnota operačního kódu je ve výstupním XML vždy velkými písmeny) a elementy
pro odpovídající počet operandů/argumentů: arg1 pro případný první argument instrukce, arg2 pro
případný druhý argument a arg3 pro případný třetí argument instrukce. Element pro argument má
povinný atribut type s možnými hodnotami int, bool, string, nil, label, type, var podle toho,
zda se jedná o literál, návěští, typ nebo proměnnou, a obsahuje textový element.
Tento textový element potom nese buď hodnotu literálu (již bez určení typu a bez znaku @),
nebo jméno návěští, nebo typ, nebo identifikátor proměnné (včetně určení rámce a @). U proměnných ponechávejte označení rámce vždy velkými písmeny (samotné jméno proměnné ponechejte beze
změny). V případě číselných literálů je zápis ponechán ve formátu ze zdrojového kódu (např. zůstanou kladná znaménka čísel nebo počáteční přebytečné nuly) a není třeba kontrolovat jejich lexikální
správnost (na rozdíl od řetězcových literálů). U literálů typu string při zápisu do XML nepřevádějte
původní escape sekvence, ale pouze pro problematické znaky v XML (např. <, >, &) využijte odpovídající XML entity (např. &lt;, &gt;, &amp;). Podobně převádějte problematické znaky vyskytující
se v identifikátorech proměnných. Literály typu bool vždy zapisujte malými písmeny jako false
nebo true.
Doporučení: Všimněte si, že analýza IPPcode19 je tzv. kontextově závislá (viz přednášky), kdy
například můžete mít klíčové slovo použito jako návěští a z kontextu je třeba rozpoznat, zda jde o
návěští nebo ne. Při tvorbě analyzátoru doporučujeme kombinovat konečně-stavové řízení a regulární
výrazy a pro generování výstupního XML využít vhodnou knihovnu.
Výstupní XML bude porovnáváno s referenčními výsledky pomocí nástroje A7Soft JExamXML12
,
viz [2].
3.2 Bonusová rozšíření
STATP Sbírání statistik zpracovaného zdrojového kódu v IPPcode19. Skript bude podporovat
parametr --stats=file pro zadání souboru file, kam se agregované statistiky budou vypisovat (po řádcích dle pořadí v dalších parametrech; na každý řádek nevypisujte nic kromě
požadovaného číselného výstupu). Parametr --loc vypíše do statistik počet řádků s instrukcemi (nepočítají se prázdné řádky, ani řádky obsahující pouze komentář, ani úvodní řádek).
Parametr --comments vypíše do statistik počet řádků, na kterých se vyskytoval komentář.
Parametr --labels vypíše do statistik počet definovaných návěští (tj. unikátních možných cílů
skoku). Parametr --jumps vypíše do statistik počet instrukcí pro podmíněné a nepodmíněné
skoky dohromady. Chybí-li při zadání --loc, --comments, --labels nebo --jumps, parametr
--stats, jedná se o chybu 10 [1 b].
