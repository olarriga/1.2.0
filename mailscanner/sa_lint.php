<?php

/*
 * MailWatch for MailScanner
 * Copyright (C) 2003-2011  Steve Freegard (steve@freegard.name)
 * Copyright (C) 2011  Garrod Alwood (garrod.alwood@lorodoes.com)
 * Copyright (C) 2014-2015  MailWatch Team (https://github.com/orgs/mailwatch/teams/team-stable)
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * In addition, as a special exception, the copyright holder gives permission to link the code of this program with
 * those files in the PEAR library that are licensed under the PHP License (or with modified versions of those files
 * that use the same license as those files), and distribute linked combinations including the two.
 * You must obey the GNU General Public License in all respects for all of the code used other than those files in the
 * PEAR library that are licensed under the PHP License. If you modify this program, you may extend this exception to
 * your version of the program, but you are not obligated to do so.
 * If you do not wish to do so, delete this exception statement from your version.
 *
 * As a special exception, you have permission to link this program with the JpGraph library and distribute executables,
 * as long as you follow the requirements of the GNU GPL in regard to all of the software in the executable aside from
 * JpGraph.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free
 * Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once("./functions.php");

session_start();
require('login.function.php');

html_start("SpamAssassin Lint", 0, false, true);

if (!$fp = popen(SA_DIR . 'spamassassin -x -D -p ' . SA_PREFS . ' --lint 2>&1', 'r')) {
    die("Cannot open pipe");
} else {
    audit_log('Run SpamAssassin lint');
}

echo "<TABLE CLASS=\"mail\" BORDER=\"0\" CELLPADDING=\"1\" CELLSPACING=\"1\" WIDTH=\"100%\">\n";
echo " <TR>\n";
echo "  <TH COLSPAN=\"2\">SpamAssassin Lint</TH>\n";
echo " </TR>\n";
// Start timer
$start = get_microtime();
$last = false;
while ($line = fgets($fp, 2096)) {
    $line = preg_replace("/\n/i", "", $line);
    $line = preg_replace("/</", "&lt;", $line);
    if ($line !== "" && $line !== " ") {
        $timer = get_microtime();
        $linet = $timer - $start;
        if (!$last) {
            $last = $linet;
        }
        // Check for 'subtests=' to add space after comma (to fit the screen)
        if (preg_match("/subtests=/i", $line)) {
            $line = str_replace(",", ", ", $line);
        }
        echo "<!-- Timer: $timer, Line Start: $linet -->\n";
        echo "    <TR>\n";
        echo "     <TD>$line</TD>\n";
        $thisone = $linet - $last;
        $last = $linet;
        if ($thisone >= 2) {
            echo "     <TD CLASS=\"lint_5\">" . round($thisone, 5) . "</TD>\n";
        } elseif ($thisone >= 1.5) {
            echo "     <TD CLASS=\"lint_4\">" . round($thisone, 5) . "</TD>\n";
        } elseif ($thisone >= 1) {
            echo "     <TD CLASS=\"lint_3\">" . round($thisone, 5) . "</TD>\n";
        } elseif ($thisone >= 0.5) {
            echo "     <TD CLASS=\"lint_2\">" . round($thisone, 5) . "</TD>\n";
        } elseif ($thisone < 0.5) {
            echo "     <TD CLASS=\"lint_1\">" . round($thisone, 5) . "</TD>\n";
        }
        echo "    </TR>\n";
    }
}
pclose($fp);
echo "   <TR>\n";
echo "    <TD><B>Finish - Total Time</B></TD>\n";
echo "    <TD ALIGN=\"RIGHT\"><B>" . round(get_microtime() - $start, 5) . "</B></TD>\n";
echo "   </TR>\n";
echo "</TABLE>\n";

// Add footer
html_end();
// Close any open db connections
dbclose();
