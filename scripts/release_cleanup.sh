#!/bin/bash
rm ../htdocs/core/V2EXInstallCore.php
rm ../htdocs/core/KijijiInstallCore.php
cp /dev/null ../res/google_adsense_top.php
cp /dev/null ../res/google_analytics.php
cp /dev/null ../res/alimama_top.php
rm ../res/hot*.html
cp /dev/null ../res/hot.html
echo "<h1 class=silver>Community Guidelines</h1>" > ../res/community_guidelines.html