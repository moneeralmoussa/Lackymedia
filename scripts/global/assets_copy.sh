# create app folders
mkdir -p web/bundles/app/css
mkdir -p web/bundles/app/js

# css files
cd web/bundles/app/css
rm *.css
cp ../../../../vendor/components/bootstrap/css/bootstrap.min.css bootstrap.min.css
cp ../../../../vendor/components/font-awesome/css/font-awesome.min.css font-awesome.min.css
ln -s ../../../../app/Resources/css/main.css main.css
if [ $? -ne 0 ]; then
    cp ../../../../app/Resources/css/main.css main.css
fi

# js files
cd ../js
rm *.js
cp ../../../../vendor/components/bootstrap/js/bootstrap.min.js bootstrap.min.js
cp ../../../../vendor/components/jquery/jquery.min.js jquery.min.js

cd ../../../../
