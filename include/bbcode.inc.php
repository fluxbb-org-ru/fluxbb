<?php

$base = $pun_config['o_base_url'];
if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/img/bbcode/b.png'))
    $btndir = $pun_config['o_base_url'].'/style/'.$pun_user['style'].'/img/bbcode/';
else
    $btndir = $pun_config['o_base_url'].'/style/Air/img/bbcode/';
$smldir = $pun_config['o_base_url'].'/img/smilies/';

echo <<<EOT
        <script type="text/javascript" src="{$pun_config['o_base_url']}/js/post.js"></script>
        <div id="bbcodewrapper">
            <span id="bbcodebuttons">
<a href="#" title="b"       onclick="return insert_text('[b]','[/b]')"><img src="{$btndir}b.png" alt="[b]" /></a>
<a href="#" title="i"       onclick="return insert_text('[i]','[/i]')"><img src="{$btndir}i.png" alt="[i]" /></a>
<a href="#" title="u"       onclick="return insert_text('[u]','[/u]')"><img src="{$btndir}u.png" alt="[u]" /></a>
<a href="#" title="s"       onclick="return insert_text('[s]','[/s]')"><img src="{$btndir}s.png" alt="[s]" /></a>
<a href="#" title="url"     onclick="return insert_text('[url]','[/url]')"><img src="{$btndir}url.png" alt="[url]" /></a>
<a href="#" title="email"   onclick="return insert_text('[email]','[/email]')"><img src="{$btndir}email.png" alt="[email]" /></a>
<a href="#" title="img"     onclick="return insert_text('[img]','[/img]')"><img src="{$btndir}img.png" alt="[img]" /></a>
<a href="#" title="list"    onclick="return insert_text('[list]','[/list]')"><img src="{$btndir}list.png" alt="[list]" /></a>
<a href="#" title="*"       onclick="return insert_text('[*]','[/*]')"><img src="{$btndir}li.png" alt="[*]" /></a>
<a href="#" title="quote"   onclick="return insert_text('[quote]','[/quote]')"><img src="{$btndir}quote.png" alt="[quote]" /></a>
<a href="#" title="code"    onclick="return insert_text('[code]','[/code]')"><img src="{$btndir}code.png" alt="[code]" /></a>
<a href="#" title="color="  onclick="return insert_text('[color=]','[/color]')"><img src="{$btndir}color.png" alt="[color=]" /></a>
            </span>
            <span id="bbcodesmilies">
<a href="#" onclick="return insert_text('', ' :) ');" ><img src="{$smldir}smile.png" alt=":)" /></a>
<a href="#" onclick="return insert_text('', ' :| ');" ><img src="{$smldir}neutral.png" alt=":|" /></a>
<a href="#" onclick="return insert_text('', ' :( ');" ><img src="{$smldir}sad.png" alt=":(" /></a>
<a href="#" onclick="return insert_text('', ' :D ');" ><img src="{$smldir}big_smile.png" alt=":D" /></a>
<a href="#" onclick="return insert_text('', ' :o ');" ><img src="{$smldir}yikes.png" alt=":o" /></a>
<a href="#" onclick="return insert_text('', ' ;) ');" ><img src="{$smldir}wink.png" alt=";)" /></a>
<a href="#" onclick="return insert_text('', ' :/ ');" ><img src="{$smldir}hmm.png" alt=":/" /></a>
<a href="#" onclick="return insert_text('', ' :P ');" ><img src="{$smldir}tongue.png" alt=":P" /></a>
<a href="#" onclick="return insert_text('', ' :lol: ');" ><img src="{$smldir}lol.png" alt=":lol:" /></a>
<a href="#" onclick="return insert_text('', ' :mad: ');" ><img src="{$smldir}mad.png" alt=":mad:" /></a>
<a href="#" onclick="return insert_text('', ' :rolleyes: ');" ><img src="{$smldir}roll.png" alt=":rolleyes:" /></a>
<a href="#" onclick="return insert_text('', ' :cool: ');" ><img src="{$smldir}cool.png" alt=":cool:" /></a>
            </span>
            <div class="clearer"></div>
        </div>

EOT;
