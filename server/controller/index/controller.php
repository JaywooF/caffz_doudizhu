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
__HALT_COMPILER(); ?>                     hall.php�$  ��d�  .��˶      	   index.php�   ��d�  �[�c�         room.php�/  ��dF	  ��v$�      �Ymo�F�� ��!t��%���&�-!��Mq\4�RP��"W��Hʊk�����.���'=�-�@��p^���]_��峧E�䮰R�܈d��Q��A �~��HH�@�~�t��\�?Mxr;���z�-��U7N�ԯ.�x�U����(�W��o��"*0�OS����3y�����'��4�]+�x?�U�f~Y~�g�2<i�܏�+k�e�>�7[���%��=�4Y���YG���Z�#MJ�8lJ	�=6nY<���a�kRr�b�݀Ђ��X[�Ԕ\�XkR��T��N,�|c����'��UJB�֍�0�|�%�̺X��'s<����������Ǳ)n��<�����Z1�� S��e��~۾W�AR�W"͚��O��4����??�uX(©H��)����
@ۿE��X�2�m6&?����#�{?��	O~�L�$��xp����,�����ð0!-#��x�lDs����T?�~���=ď���h���=y��8�7b"�'���D�KY��H�
(�r}���䧧���uLdv�T�e~w[�9UΓ�0�����C� �@�Qȫ ����5�*�N	�@�.��&KaӌI�$n��v��U��	r��c)d�)qh��M�-U�P�n�AM!�g~(���e���ی��/v�/�����F仟@;	E��=���L}���٢I�@�! ��CҵZz<3����1$l�$�#x������4\��DV4S��)ə����OB���&;u~}�����o��-i����T�C��G~R+?9\���I2zck�]4�Cȯ0 �����p#�H���j��}̻k#�]
�ɏ�4Kx�'���F�p�Ua�lrE�,��7����_���z�d~����b�f����3�̑�E�>]Z=kh�V��]\j0K����wǆ�����w�ɴ��E���\�~��A2�.��C�����"�wrSMv� ��.�m�m�z���)+���:<�WfXI���X���g�����`��o;l�'8��*�T�lT����m)1Z+�q����U�/��cr��5�& a�eH`#�*�j���~+J�ċH]�#m=�yT����Ŷ-��ٰOn��k��_Y�kEэ�!��H���s��^�O��(x�=�������p�2K��7 �h�vy
�>��A�3֌%Y��k�r
ʔ�'�"HŮH�p��+C�'k~k�_��W8b��{/z�Յ{�U�9{uV"\̺]E()�����Q�Z%�]�%��H�.|�h��_C����� /�=�G]���s�q�y)Ż�G��[��E�e��_-b��������(�*�_q�R��.@P9.�ko�)�5��l�X �14�j�Sc�K���w�ۄXK`�_��SG�T �PM黣�<TH_n���_�JzǢC.���\.���d+p�5!��M�6��ͶݓWj��}������,0,aO�(`W���t͑6��D������$�E��L��1�PE�V	nN�J���K�pHR��7��AAC�Ni�q
sc�>����)�:ösm������a�ˡ%�ÛV�L��@�$.I\�6�s�OG�u���)]��r%�D�J�zRi�/4V����ʥ�(��K Ӽ�vd.�6����L�W�Xg'��{��J@(ɱ1�,9�=z��0g����x9���?���Q���_�����A�C���}������Q�C�%��#�깿W>�Q���_`?�|u���ďR�T�u��x��R��RI�ԗ]ԱT]ԱlLT����4��J�E��d�$ޤ��������#���J�xPs����t�L��7jc����U{���e�!h�Ȧ�'g!��|�n�u���K�;�z!�0z�p��@M$�D`���K�q"�CS�4�m�����D�Vn$%h�d.���Uf�w�n��o�w�o�q`Z�@�z�=�����Ӄ�A��LW��Ռ�t ���7��k�?J�k5����h��	u(����c��8Ŧ 5���nw��0ԭo�R���twtUj��ios�<�MӞ�h�d5��e"KX�5GR6]�#e~�UZ�}�c�52�l�/����n������<L�ZϣR1mhi��ʺׅY�Y�܆�<"t�8��f0�ȭ���u�{�vz���I�|@@��ן���'��⌡��w8e([������ko�6�s�?p@ �K���+q״n,ú�H����D�\eIѣNV�d�Q��m�u+`�yw����G�������$r\F`��A��Cx�'��gO�<Β��؉���-�4�W�0f�R�y�孬�C��Vfi�?q"f���$!K	�LY�)��O?����]��N
�,pS�<5j�(rb�������'��,Ϙ�ؠ~�:�4������A;�����}Z�=Ez[A[1�\�Ap�1���M������<�׶�^�;������i�ʺfǃ�t�	��>BN�-c�X4ҙ�nP4���f���tbe�i�řf�h�{f���o��g���ޤ;���l7�&�!i�1�t���r��i�cc�}6>g��B'F.����Y�f�:�.@��� %�/4 �Z[ROT�	�5R'r����4�B�V?HXjP�B�P)sIW���>����(yv@t���Μ@KK���'��g�ND4na�9�!�Kq}�cI�Z[?�9~9|s�\�Μ�#:q�� 
,�������R��A�%��q;
�VN��o�N�	?�.� 1��X�K�Xj�]�Ax�μ\��� ��tz�����c\����-@�"q�(�V��ty�g������s*��&��al��'�o������������_��u�ˡ���|<w��Aw�#��װ�G��?�Vݮm[/mVN5j�ڜ����\׶�vEw2�	|��3CL�F�ߕ���.�Vݺ�3[���xJo��{u7�����ln�ʖ!9m��Ycw��d<˩��S��	��������?�
��AR�O%��tʄF�\TAaMD�{v%�`D��tN� �E�*Q�]'��5 �\ 	�BD�����c2�TUh�n��m��W�� �|��8��h\�Q�D�E؄�@,��(�,)�񩡫WRup�J�2`uݢ�jb)�z��d���tk�HڂGvMf�G̝�����5���0`~K�T��%�#[�!��R��n��pq�u1�jŖ����z�UZ�(K.���}\p�(�@�v��i���R�Ͻ��$�,m�@��$��B�bA�h��n+�?B�d��LS�H�.�+�w4�- *��iB��T��J ��w4s�ނ��!wV@�p��=�yV4��l���n�`?՜�����$z�t�|R8��_ᙹ%�#)�MnwuH�#�t[6���d�d�����������i�l��%��l�[mv]����_s��)�2��!�������?Ѽ��^�������o��m(��=��:����@�&C1~�c)�F��"���,��#���-ZQ2�2\ƽه���&�C���2��W�E5Q�s6 (iĜb,�����cYg~���9�ABk�a��b�ͪgي_C�Tb߷p�ۖ��&�,t؇� s�?ƅ�z5,��{��浤�:�VP�5᫉n��-gc�{0-�.7�V'S���ʧ�˒�%�y�yP�Z��5���Ѹ�Z�_���{
ROvw}�d����ܿ,�T�Y��8�~}��[b���_�U�l2�?��[��*�ʧyZ��Ֆ0��=�&�'4�QXd��hE���%��Ǆ�yׇ9���\�ܘ����~�S�Eq��Ȥ<��n${f�YY���s������w��M���G˄�e`���������d[���M���N[ȋ�hPq���Mhs�]����[SDo;V�l�����f�w�՝i�7��<fp̘9�`wwy}&/,��n���C\ܢ�p@�|����,u���[�<��h�f����e	�P��(�!
cAx�m�.�/�^ݛ�����o�'#ztv�v|t|z&����E�^�Ē1�0<87��ځS�o��v%<�3iu�VX����o�ko�6�s
�?�AP:��ZJ�r��6��>l��ep�@��X�,���6h��ww|��ñw0�UHy��C��b��Q.E�
#�
���y��e�%	�t&>�B�%���G���J�i����c��\%��(�Nڡ�Yv>����L$�y�.��,,���(	��U2��Lm�����j=M�eX�:��8KY��e�7%�e��1[��H�ш��0C�pb!�{<ɢ��@���͟o�ա<˖B��$�	�g<��m�z������"L��Y�U���`ԓ�x;y]����*�gte�����	_d �36�+"@ю� `# \���Y4�� b8��Ư��S��h�������������0A�7ݚ}���f�����*�֢({^fW"EV/~���_~�ib99�[�~E,�G��b����� )=P�GfP�a:�d�
��Q!�p��!gO��F:S�ix�#`��V��K]Nq����r �n�H�D���ӧ��
�8;��8�E)�5�rE,nW�>�I!��&<�������D#?"��J�4��Z �4u/+�֫Y�%o�(��%605�Ǘi�����u�U\��J�`�|�&��ߏOz�h�1��[<�����G��J���އI��.E��f=� D��"���G@^��}v�g/���e�Dr�Oo�^�8��ˮ�~2�~��M+N!���A�6�:�V:��"p�4&<��e)d�bP0������ ����x�U=O Au8��>�K��6�l���{�%�p�!��}����nttE�$ꞌ[� ok[�ix}��U(+}���t��y�����]���s�b����Ә���,

ۄ���l��0�NAŨjg]^A�wuGUxk;ig�%�I���S���S�-_9ݳ�ܭ���H��PA�=HZ4��ɦ�ʡ�2��I���u�~�.SCFa>��:=��������X�e�����O��v��zQ��؈�F5�����Ϻ q�����w���0���d���Ck�.Nk�>.F��S:��2>��#{4h�2�b)�K�S�֪���t�N#w�\'���$�8��Ю_ъ,�c7N@/4�K҆�L���N�'3�v�
��[��О���n��A���t ��U��CDoC�_ �j�`D3�l���>Vk�j�:e��ǲ\��~:4!N�/�B�(����"�\�lWbpHm�5���1�\���Z(���M�xO��,6��/��L�5�_[�Du8+��nH�ݐ�;Q��=���ә�^�4�ѭ�J�wO�ھ:��K�_,��Gq�K.�yi<�������W�'jp���u�Sw>��&�K��B�Ħ�V0��-����5���⠝C�ѭ�{�Hb��*I'wI������5��Jó�����mg6�XU!2�V�Ԅ?�x���K����-O�%��5�E��XEj�Z]��0O�vT
��@�����!v�kj���z�����G/�I��+|�����(��=>��Ƿ��_��>N��_n+������Uv�t��0�#�j�6[������n���4�2�cNBhˈ���ݺ�rPˬ��ލ�\����i�w��{=q�Ng�~����#�C�GV�!���T2qG� �=;��(�W7�;���U�����hBt�jB$��BDW=	����:j��&Y&n�;i��m��l߅�Jz|���v{�kܩT���u\i�̲�RNM4;��a]+�����V�ū�b]��s�V���]���K��p����`Ufb�*������R��=���w��̋t��~�ɵ�1�5:��s� O�G4�*sA��r����Ԓ�k7Veqp�p��`��S̞镑Z	�s���;��pI"h�A+mD��T	�����w{[d:��	Y!�V�h	�j�"(5�I��=��s�vMQD"N�=ݍ��
lcp��4����jU*nd�����\��3.�r��K�K����K����M�?�<>�Oa`�-�?0� �!'�	�8�G{��=b���jpdb�	�:�����NS���hv��i���!����Ӻ8�x7���+D�Narxc����P����Ņ��7���z��hV�������V_��ww�������[�֩��s\��J�y�Z�{nGr���ӗy�^a�I2�&iD�?ĚĂ�H�@p4����U�\sJG�kk�z���0�X�Q�u��e]�<�FZ��������F�dhk\�V�(/C���)���gU�o��ی.�]�������H�WB��i�'�߅5(��i*��@��%�D�^aHF�*Fm�@
�ր#2*��`�ot��g��1	�Im��,!�I���_�B�J�B�?l�5帓H   GBMB