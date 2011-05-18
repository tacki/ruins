<tr>
    <td colspan='5' height='30' background='{$mytemplatedir}/images/bg.png'></td>
</tr>
<tr>
    <td class='pagebottom'>
        <table border='0' cellspacing='0' width='100%'>
            <tr>
                <td width='27' height='30' background='{$mytemplatedir}/images/s_top_upper_left.png'></td>
                <td colspan='3' height='30' background='{$mytemplatedir}/images/s_top_bar_up.png'></td>
                <td width='28' height='30' background='{$mytemplatedir}/images/s_top_upper_right.png'></td>
            </tr>
            <tr>
                <td width='27' background='{$mytemplatedir}/images/s_top_bar_left.png'></td>
                <td align='left' width='200'>{$version}</td>
                <td align='center' width='*'>{$copyright}</td>
                <td align='right' width='200'>&nbsp;</td>
                <td width='28' background='{$mytemplatedir}/images/s_top_bar_right.png'></td>
            </tr>
            <tr>
                <td width='27' height='30' background='{$mytemplatedir}/images/s_top_down_left.png'></td>
                <td colspan='3' height='30' background='{$mytemplatedir}/images/s_top_bar_down.png'></td>
                <td width='28' height='30' background='{$mytemplatedir}/images/s_top_down_right.png'></td>
            </tr>
        </table>
    </td>
</tr>
</table>
{*
    maintable is closed
*}
<table border='0' cellpadding='0' cellspacing='0' width='100%'>
    <tr>
        <td align='left'>Servertime: <span id='serverTime'>{$servertime}</span></td>
        <td align='right'>generated in {$pagegen}ms</td>
    </tr>
</table>
</body>
</html>
