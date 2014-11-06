gameApk=${1}
channelApk=${2}

channelDir="tmpChannel/"
echo "remove $channelDir"
rm -rf $channelDir
apktool d $channelApk $channelDir
if [ ! -d "$channelDir" ]; 
then
echo "channel $channelDir not found!"
exit 1
fi

echo "remove tmp folder."
rm -rf tmp
apktool d -f $gameApk tmp/

rm ResultPublic.xml
php merge_public.php $channelDir/res/values/public.xml tmp/res/values/public.xml
mv ResultPublic.xml tmp/res/values/public.xml

php pack_base.php $channelDir/AndroidManifest.xml tmp/AndroidManifest.xml
mv ResultAndroidManifest.xml tmp/AndroidManifest.xml

rsync -vzrtopgu -progress $channelDir/lib/ tmp/lib/
rsync -vzrtopgu -progress $channelDir/res/ tmp/res/
rsync -vzrtopgu -progress $channelDir/smali/ tmp/smali/

echo "remove tmp/build folder."
rm -rf tmp/build
apktool b tmp output/output.apk
jarsigner -keystore key/denachina.key -storepass denadena01 output/output.apk dena