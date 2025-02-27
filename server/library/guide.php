<?php

$web = 'index.php';

if (in_array('phar', stream_get_wrappers()) && class_exists('Phar', 0)) {
Phar::interceptFileFuncs();
set_include_path('phar://' . __FILE__ . PATH_SEPARATOR . get_include_path());
Phar::webPhar(null, $web);
include 'phar://' . __FILE__ . '/' . Extract_Phar::START;
return;
}

if (@(isset($_SERVER['REQUEST_URI']) && isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST'))) {
Extract_Phar::go(true);
$mimes = array(
'phps' => 2,
'c' => 'text/plain',
'cc' => 'text/plain',
'cpp' => 'text/plain',
'c++' => 'text/plain',
'dtd' => 'text/plain',
'h' => 'text/plain',
'log' => 'text/plain',
'rng' => 'text/plain',
'txt' => 'text/plain',
'xsd' => 'text/plain',
'php' => 1,
'inc' => 1,
'avi' => 'video/avi',
'bmp' => 'image/bmp',
'css' => 'text/css',
'gif' => 'image/gif',
'htm' => 'text/html',
'html' => 'text/html',
'htmls' => 'text/html',
'ico' => 'image/x-ico',
'jpe' => 'image/jpeg',
'jpg' => 'image/jpeg',
'jpeg' => 'image/jpeg',
'js' => 'application/x-javascript',
'midi' => 'audio/midi',
'mid' => 'audio/midi',
'mod' => 'audio/mod',
'mov' => 'movie/quicktime',
'mp3' => 'audio/mp3',
'mpg' => 'video/mpeg',
'mpeg' => 'video/mpeg',
'pdf' => 'application/pdf',
'png' => 'image/png',
'swf' => 'application/shockwave-flash',
'tif' => 'image/tiff',
'tiff' => 'image/tiff',
'wav' => 'audio/wav',
'xbm' => 'image/xbm',
'xml' => 'text/xml',
);

header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$basename = basename(__FILE__);
if (!strpos($_SERVER['REQUEST_URI'], $basename)) {
chdir(Extract_Phar::$temp);
include $web;
return;
}
$pt = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $basename) + strlen($basename));
if (!$pt || $pt == '/') {
$pt = $web;
header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . $_SERVER['REQUEST_URI'] . '/' . $pt);
exit;
}
$a = realpath(Extract_Phar::$temp . DIRECTORY_SEPARATOR . $pt);
if (!$a || strlen(dirname($a)) < strlen(Extract_Phar::$temp)) {
header('HTTP/1.0 404 Not Found');
echo "<html>\n <head>\n  <title>File Not Found<title>\n </head>\n <body>\n  <h1>404 - File Not Found</h1>\n </body>\n</html>";
exit;
}
$b = pathinfo($a);
if (!isset($b['extension'])) {
header('Content-Type: text/plain');
header('Content-Length: ' . filesize($a));
readfile($a);
exit;
}
if (isset($mimes[$b['extension']])) {
if ($mimes[$b['extension']] === 1) {
include $a;
exit;
}
if ($mimes[$b['extension']] === 2) {
highlight_file($a);
exit;
}
header('Content-Type: ' .$mimes[$b['extension']]);
header('Content-Length: ' . filesize($a));
readfile($a);
exit;
}
}

class Extract_Phar
{
static $temp;
static $origdir;
const GZ = 0x1000;
const BZ2 = 0x2000;
const MASK = 0x3000;
const START = 'index.php';
const LEN = 6643;

static function go($return = false)
{
$fp = fopen(__FILE__, 'rb');
fseek($fp, self::LEN);
$L = unpack('V', $a = fread($fp, 4));
$m = '';

do {
$read = 8192;
if ($L[1] - strlen($m) < 8192) {
$read = $L[1] - strlen($m);
}
$last = fread($fp, $read);
$m .= $last;
} while (strlen($last) && strlen($m) < $L[1]);

if (strlen($m) < $L[1]) {
die('ERROR: manifest length read was "' .
strlen($m) .'" should be "' .
$L[1] . '"');
}

$info = self::_unpack($m);
$f = $info['c'];

if ($f & self::GZ) {
if (!function_exists('gzinflate')) {
die('Error: zlib extension is not enabled -' .
' gzinflate() function needed for zlib-compressed .phars');
}
}

if ($f & self::BZ2) {
if (!function_exists('bzdecompress')) {
die('Error: bzip2 extension is not enabled -' .
' bzdecompress() function needed for bz2-compressed .phars');
}
}

$temp = self::tmpdir();

if (!$temp || !is_writable($temp)) {
$sessionpath = session_save_path();
if (strpos ($sessionpath, ";") !== false)
$sessionpath = substr ($sessionpath, strpos ($sessionpath, ";")+1);
if (!file_exists($sessionpath) || !is_dir($sessionpath)) {
die('Could not locate temporary directory to extract phar');
}
$temp = $sessionpath;
}

$temp .= '/pharextract/'.basename(__FILE__, '.phar');
self::$temp = $temp;
self::$origdir = getcwd();
@mkdir($temp, 0777, true);
$temp = realpath($temp);

if (!file_exists($temp . DIRECTORY_SEPARATOR . md5_file(__FILE__))) {
self::_removeTmpFiles($temp, getcwd());
@mkdir($temp, 0777, true);
@file_put_contents($temp . '/' . md5_file(__FILE__), '');

foreach ($info['m'] as $path => $file) {
$a = !file_exists(dirname($temp . '/' . $path));
@mkdir(dirname($temp . '/' . $path), 0777, true);
clearstatcache();

if ($path[strlen($path) - 1] == '/') {
@mkdir($temp . '/' . $path, 0777);
} else {
file_put_contents($temp . '/' . $path, self::extractFile($path, $file, $fp));
@chmod($temp . '/' . $path, 0666);
}
}
}

chdir($temp);

if (!$return) {
include self::START;
}
}

static function tmpdir()
{
if (strpos(PHP_OS, 'WIN') !== false) {
if ($var = getenv('TMP') ? getenv('TMP') : getenv('TEMP')) {
return $var;
}
if (is_dir('/temp') || mkdir('/temp')) {
return realpath('/temp');
}
return false;
}
if ($var = getenv('TMPDIR')) {
return $var;
}
return realpath('/tmp');
}

static function _unpack($m)
{
$info = unpack('V', substr($m, 0, 4));
 $l = unpack('V', substr($m, 10, 4));
$m = substr($m, 14 + $l[1]);
$s = unpack('V', substr($m, 0, 4));
$o = 0;
$start = 4 + $s[1];
$ret['c'] = 0;

for ($i = 0; $i < $info[1]; $i++) {
 $len = unpack('V', substr($m, $start, 4));
$start += 4;
 $savepath = substr($m, $start, $len[1]);
$start += $len[1];
   $ret['m'][$savepath] = array_values(unpack('Va/Vb/Vc/Vd/Ve/Vf', substr($m, $start, 24)));
$ret['m'][$savepath][3] = sprintf('%u', $ret['m'][$savepath][3]
& 0xffffffff);
$ret['m'][$savepath][7] = $o;
$o += $ret['m'][$savepath][2];
$start += 24 + $ret['m'][$savepath][5];
$ret['c'] |= $ret['m'][$savepath][4] & self::MASK;
}
return $ret;
}

static function extractFile($path, $entry, $fp)
{
$data = '';
$c = $entry[2];

while ($c) {
if ($c < 8192) {
$data .= @fread($fp, $c);
$c = 0;
} else {
$c -= 8192;
$data .= @fread($fp, 8192);
}
}

if ($entry[4] & self::GZ) {
$data = gzinflate($data);
} elseif ($entry[4] & self::BZ2) {
$data = bzdecompress($data);
}

if (strlen($data) != $entry[0]) {
die("Invalid internal .phar file (size error " . strlen($data) . " != " .
$stat[7] . ")");
}

if ($entry[3] != sprintf("%u", crc32($data) & 0xffffffff)) {
die("Invalid internal .phar file (checksum error)");
}

return $data;
}

static function _removeTmpFiles($temp, $origdir)
{
chdir($temp);

foreach (glob('*') as $f) {
if (file_exists($f)) {
is_dir($f) ? @rmdir($f) : @unlink($f);
if (file_exists($f) && is_dir($f)) {
self::_removeTmpFiles($f, getcwd());
}
}
}

@rmdir($temp);
clearstatcache();
chdir($origdir);
}
}

Extract_Phar::go();
__HALT_COMPILER(); ?>
�                     db.php�  ���dc  ��"M�      	   index.phpp  ���d	  s6q��         lib.php�  ���dz  -�dK�         tpl.php�  ���dF  ��T�      �W[o�J~&R�����-Ф�$�8
:\"0�� QK�0��6���?3����ػ��of���p]����g�Y�3FB��Δ��e����M�������|�� r|/?J� 3s�0$����\��G;�Ȏ�������!�H�����qS�����u�Wz�ѻn��\���}�w@��ܠo��=eо�A�R�N�m�����j7-�O~��f�#�u)�E�E�s�E��=`_���[B�ʴ2wQ*)ʊ�
�.����/j	>�3e��<O�)җ�O!�:���Rφј�>Ǟ�H�X����6C������w8xZ2�t���~�h�X&�0pt9�j�sf�R��!�V�\!ڣ2���0�,p!�u�E�?�}����B�j_ᩭ�q9g������m(�F�OZ�V�*�(��t��ʽ27d �x	��b�'Cz�
+���ϲX(��	�P���r��I| e ���q$���J�"5�!0ϏNA�t�S]g�N�l5�5Ɉ~���x�!, _*���H̄b�H?�qB�{����F�2��[zl��G��V��(�sj����"g}���^�=t��Y�vF��O�Q᜔�8��N>~$N��5�I�����J��P�P����wfτ]Ҽ�8�7S�~7���	᭪��GGQQjQĝ����S*�,�7�����M���v�#jf�zm{s]�b�����2�ā$=�|p��+T�3���1C�T�J�\.Z�&��I�� �>ozЃ1w���j�R�`_8̝�[Zb��|θ��ډ�pa;n�BZ��h����+��7�4xå�@�i��)e1�~��S?Z�}�l�%�Tߒz͞ןCX�i|L_Q�gˍ���K���5���
�u�Fg�E��h�nb��͛��߯x�������T��<� �Z�:��	&%B?��횵�j�"4,�YJaL�!��p��B���z�qr�e)U��Z/���Aq9��n�a������2^;P��3_~A��h"�M��l7-L�$����r���k���v*bQ�}�e��`B�,�hdқ�]WQ��,���� Jo��
�Rƽ�B�p��I���LAQonM�~7��Zw�i�s�`UK��*h�j�4�4���3h���U�BM%���c�-�2�p�!=�~�[GGnI�\7+To�رR5g.�Ԫ�F��r'E�V$��K��'�?f�&wlb�(	o-��_��ݹ_Ӳ
p�n�c�ޱ�o�QU@b������w9�	?�D����|�x�#��rĊa��m��~��}ݵdK�f>��� ����L�@�hQ�Q�����N̄F�|��f_�ֶ���D��\(���QS0֜�1��W�����K�0��-��a�Z���Z&V��J7�l�&�`��$Ձ������T�^������YZWu�+���!W���el%���Rl��Q�h)J��?�r�xN�К�pBYq�+��-o���:���hT@	�Z˧F�ֲwqV�]�P8���M��
���=�j�@촡RBb�a�+��!��-2R��2��+Wxk���mwý����u��k�o�-z(I�9l�8}�sP����p�?gp�h�i#�?N���X�k�G���Wio�F�� �,*�d#�[$v�9�HrQ@T	�ZY�(�].����{gfw)�:�l�ؙ7�ۙ��n:K�?���R?`F��7��(s�߹A����gA�g�/�?�Ky�� *|F-��<c�85����wqu��;����H(�i"Lb#Hb�!�Z&xݨ%qtw:�����WK��q��g����ͺa���ěw��vG�}0G'��� ���-���5$Ȱ=2��>I���[�����e��99����S�yO{���(4a��2d�&ć��O�H�Jg� �Ubg�����ϯ:'�Fb����xnB������˗��:h����SK��t�3������'�,p��,���rRJ��ރ�]7��������ˬ>�R�~#n���ބ[)�������ؐn�������csK�R�n��/���G�e6]�͇�rt��}�fn�K�iJl�%Q�Ʉ�M(|���a�1�%	�q��em�6Σ9rz%1���
��ʟ�/�*�j�6�I��3n���i�[Y>o-����������<u�|����&UJj�t�#�_߱Q�\�:��}`P���&�-A�-	K
1c�;T7ej�?ʍ�Ϲ��	��Q(,e`[�A0bq!emZ9ş�{]{]/�+�4�����z+��.1C��1Z�Z�L77�"Ji,��n`4�?�V�A�v!Ht�����}9��>:oOHn:�R�ŏ:Z7���6Ի�{��}*r+f�Md�?����d'(:o�����WZ��S[�1�LԴ�FJ��֝(�P�H
����]qI�_A�*��M���C� �vt� "+��jLF�Z��V�A\�a	�(.e䵄��g|��_�Q/a�w�8 X���SU� ��&f	�q�� �9ci��2��[��N�w�74{���?�>9���Ks��	�߆��?0����A��� ��4D##���7JŮ,������T��NP�o¦~�͆�9��V�L�R�)̢|��$H�X���f�ȓ�%���Y�"���t���ݚp:Q�U�A�-RqWҹ�x=��szP"�Te��9"ͺ�n�m��GzW�=j�n��݆���� � ��V���e9��b4�h���̮�}�����3E0'�U���Ll��6�U61��X�N�4ϰ�G�Td>���g0�[���#�"��Ȓ��@�jA�Ei�G<���@���
F�PJ����-9؆�F��&���X��Hsi��8��I��a���ƕ<
�+�3����i�s�\7>^_|��Wv��7�҇�y�L��pDΤ=j7��|.V�(ɘ^�o5-��[=
?�[�t�(����l%�*���J����#�o���!b>����^ã|G�.
�u.�T&�3(�,H �'$�|Z�ۧ���*��n?���L�FC�����Y4m�E��_5X-�b2�)�W���(��TJ��Ė�2|QD_${Gn�w���������\��3>��'yy'/�s�Ề���i��ڭΑ�JY=V���Q���+���l�+rZm���8h��*�>�&J�)�L{L{'�6��)��.�����h���M�I�S�9=�e��R����X�O���]^Ҝ��Sö�b�(��C�r'����۰��W>��S7L���|����Tmk�0��@��>�:��X��m�RcY�Ed˓䤥��N��8[!8�=w���s�p[��d\&�UB�L�z|�����l?���H�&�/��R|�F�М�O-�)Sg�]�Ȃ��3��Ҁ�������j�ӂ�FpJ����˒�1��6��f�\�đ"�u�a#�M[S�>r+*�똜��%�n]	��!B��؝���ɚ�BWV�t�b���J�r#����@��3.Xo������Y$j��^Ԑa�'|-YD���1f{�[��C�Y^\rYz亯�=U\!����I�
l5C��dЙr�.�2!鶋�ɷ������e�l�,c�cL?2�{3�4z��y9�����
�Y����E4�G�	�^�zh��/�����F�Ј[�z�NτD��B<	�@������VY�-koٳ�� �ᵹ�>�E�[ /�x�8£���idee34��X�D�i;��� r6���n -�'R��_Q�i �T��^@}r3��|��-�������ǻ߽3�()���>7��g{�O�Ù^����lu4��C7aV�q&j��S>Q��}h� ���_ĉ������\X���0{h0��ϱ��&�ހ   GBMB