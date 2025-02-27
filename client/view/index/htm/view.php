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
__HALT_COMPILER(); ?>�                     hall.htm�(  ��d�  �M�      	   index.htm�  ��dR  d�0�      
   notice.htm�  ��d�  iRN�         room.htm4#  ��d  ���'�      �Z�oG�R��멒�*�%P��خZ���HTj_*�����^��ޭc�'@M0����������$)�1����Bgw��}��N�P՗xo?f~3;3;���;����7_�гo��f����BF���RM�wb��_S$�E丘f��Gvі�Z��wi����r%T�ti�04�����GY���D��s�mˡ��)6�^�h����Qq�L�P����"gf&t�%��]r��?P��9J�������BZ_�����D�m�N�1�AsH�ʒ���-
iT� d�F�QEG��Q�|[����WΦA�5p�_S�_
15|L9�*E��=lub����y���CC��gd����-b�CR�	���g���%Q�bF�!�e�:���u�}~���C	y촭yWZ#s��#���p���ٕvmdv��P�Q�bb�*L"Ò�d��8�hF�I~�U-'�M&����)&JR71)u�s|� f�&��)� u�@L�A6K`L|>1��M,���#�]�F?e�P��.w�h ��v�l�|i�k(�'%�o��R�yxR:������四?��A�y�R��"�1=)��ؔ�����������v�gF��pY���`��. �O�W6�~_��#�xY�_~�=Xl�:�~Z �%���+Z�z��q�!��La lk�.Q_ogny�ꕗ�מp�Unxk��w'�܁��r��˝��NȾ;6���=�Y�v�v��NN0���A~�>���
��|0�^�5�����=+b�!���?���W�hu��c*��7�y�����`��5R2�p��y܂�x���P���g�a3��ΓB��w3pr�('��y��E4����16*`&|�,G���D�r��v��؂�X��=X���hP!Y�dB����jU���Đ@��Qaf�r���Ui����n)o*���#�{LK�)�"���A��L�\F�	����g����P�b��d��Fπ%^Xdd��6�R����LGIԃE�=cb؎Up0+d"Y��;b�+�� �,.�/�R0I�
�%q-��|rrbu���\���-�� ��p6�*��V-=eh����Af�)B�\�C������j��ȫ]�����ך�V+���K��fF� P��+h|�x��%��u�e��m���^��+K��O+�Q�V]&�.�|��C�H�v��r�|�R�l������b�3v�%�~KΊ�<����F�V����+�<v�����v̪������Em2�l T���k��r�oo\;u�Y}1>���Ҹ]mVŉf��^Ԟ��=/�c>[+r��5H�` ����F���#+��b�њ��ٞ�w��U��4��m������	�@̲)���C쌜wiE���[Ίی��p$rv[�{q[v��ý�C[.VL��"t�|�G4�`ph��4Uz_�i8��=�	��Uۘ����!g݂Qf�m���.<�P�N<�f��n��2���H3hK�#f��؂���(k vֻx���B�׳���کd�0
]��Σ��FmU������s�]����~�_��[�
������F�Z��r��W�'D���Yc��F�lc����"P�N-z� Ty����?9�lcm�[�5.ܯ?�2�o�-0Xc�2�L@"�	e�6҇���R������F�h�E����_]��V�@#Z��?n�j`J����^��o����re��;�۟��;�x�Ub�����<���L�(X#1�����u��.���e�����}�+q-'?Y�7?��W�P<?'iG@X���˷!���~5ޅE[g�ވ4����Y���wX��q����v�[_�(��p�R��bt��q�ҙ��k�A�T���O)��@�<7Z�������1�<�"�K��w.m�X�����a�g�X��X]&���I0��n���[�C^v{�����8���0{_���V��̬��lT����?��=>`��T�
����XMoE>��0��Y/\���/q�T�rAU�g��If?��1'��V�F�T	�@�BE|�J~['��wv���+��Uȗx���yޙy�q\�ݛ�|���PG���Ku���>�$ti��AF�1M�TL�����)b4L�5r�6�����$Vڹ�йsm5�(>&��g�4�#X�Yx]������;�<��*J��،�>�:Ѩ�QL�҄���$���Md�J$e��߸��[7n�vR"Id$7���4��������I�:t)�����qN�|��{6 ,,ҽ$j����G�(FJ��G�Z\*8$奄�z����v>�0٫�(��
X�WBjn����{;�6fx�wQ(Y���*���d��J�S!c�@)/��0{E6�����kV�ap6�*%1��(��\��Ա+�v�Q�V��c�Pb.h-��Z�1�N�fx+���cS z���|� ���eެ���d�mä��������?{�Sv�S=(���b�|� ~�";|2��iv��p��|<�O���æ�pY���3�[n;�-�F��Ȩ 1&Bx̤ICu��6��qe�l"��b1m����鎌Q��,��tH*��ptb�&��bnb҈v�f�hv�����X �d�LÍf��%���r�⡱F,j2�m:��Nh��i�JZ-�RA(0�������o�__f���[�c�H��4��$R2�T"7��^����w��f{��̅+%)�2�;{t��p���3�Q��-"��r��	��Hp©��W��e�����צm�ˋ��o95�#V���Ja�GTj��i�[��u���v��n}�����j���ְ7�����0C�E0J��$�2H� ��g�g������c�?)����<s)s-r�����/�H�6
��]Լ?k}�=��<8�_���]p#�/&\κ������k�_��R�7�ٰ߁/h���/�{�	]�|�����K�x��K8�r�O���������_�(x�ms�}rja�+Su�2����
{����=�����A�+���?��m���_��񫖿I�������������S�n�0>/��ZH{�6 �F��ĥR����j5�Lף:Nd��q��p�ƕ�}���q�[���p�g���8��볓߽Q�kW>}��S9��B�#�uw�P��Fe,��\�����Bc��-�Q9�p>4��<���o^�TG���{���s�E��J���-�b[T8'��.�'OL�Fр���~K�g�m>��&�?�+r��E��#�k������������"�l���0�@-S���z�������{p��H�p䯔x��jZ`��#1��ud�+\f&��7,K8�P���БW�E���0.9�v���4�J�A^�\1�^qF�:NS��~���B_�t�� y~�~�_��*)�¥�qP�����z��z���q7S]��]�������7���Ǩt�t�����.oC��l3@QK?��Z[oG~N������)�z�@y��@�R!��c�Ļ;��8�[U
��
D��� (miC��*�B~L����_���%���1[�/��������9gƳV���̩/��ⴤ�ȼ��ʾ%X�)�k=�Y2� ǾMH����q!��Փ�nKPӱ�.ё����lJÖKR�h�L�;!����&��g,��!�D�	���2�}*g���aY� `�]p�ظ	�Y2��� ��y�!�0S����[U�'��j��D*6�N�Qf�,�{e�u������h��hE� �(3�KЩL̸rFU|�G`��Q�^
�rpN�qc�ˬ����;0�L���iC�hL�.���!��[f��a%��,�U�  ��\OL9#�rAh��i���"��qӧΜ����gS&4��9L�5�f%� �;%3m�5��������O� YR{W���"^;� {<$�`�'��$���O`?N��(���`�O�}O�-(�(E�I'O�g�U�E#�xd��>_B<�z� �*.l,�hik'hgN�󋚪��)�%��2��^�<�	�gru���"tJ��c?0x�_�x�lAGdeq>O7E��1����9Bf!�V��Z_�m�.�E��Q��VX���p�6_�A�H#��u! �2fQ�3壮IAme�P�Y��|�W�8��RY��b��uE�l�9��Z����m�}���o�(�;z�|� �t��W�qeC&Ǽ��Ƨ.�j&( �� �B�.#K,��a�/.4�ࢯ�ZGS�z�G�:���t�zecd�z5�KUtC���"�uJ�P.CP6f؍����QM�p40aѕf����dE#���A��8_�5k�&Tֹ�0;���_�^��5�t�qɅ/��T�e��s�aL��a��uo�bB-7]�e�N`�j$�0Dk�I�+#9�l�"�r���~�5�혲@OB\^���~nܹ�XZ�/�x�o{~��ն_�^��uk(Bh�
��6��|��bw�[��^]��ރ�j���u����ws��Wk{k�����'�H�Ғ��x ��8�P�����g��G�L�z���%Ӽ�y��^s�~�����}u�eth؃d��0:e�mo��↘�%�c��ي�S*td��y)k"��NP�軣)v���`�?�W��q��������N8���q5�𦥃w9�m�ְ�6�}�?�6�Ȧ�f4:� �L��������~�^U��H��b�����4�+�9�\�z���oE
l�ĖQ947�b� lI��(Qm��Ls�ǃ�;����<
��S�ƾ�y��|�\ؒS'����9�|����e��-������o~�{�xp�a��e�Zߝ�-�t��w�.*�ii�6�L˖�V�c`������]R���`
��#c�6����آ��V����� ��
�Ը��5�7��[왺!՝��$$��i��!��Xwa�}�).���*L��HX�FᯗT����n6S�Ϳ� hǆ���z�0^_~����Z�P&���1蚀Ucom����b�Nm늷9�]��֗�؜_���z����l3[���ǽ��v��O����.�Hr��o�*�@m�|j[����g��;�گ޵����ƽyJ��l��󰶽<)hg1�_s �����m��۾����١�����i6��4��g�?,S�xF���հ��N���M�U��Cݭǳ�v���+��du���J����"�r�9�q�R�?�Պp}������;����y��C&:�v�I���ut�؍Obk�I5ж�	Qr}��V~�W�'�� \9��ﻴ(1�z(�`[�����:	�����   GBMB