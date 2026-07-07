for FILE in *; do
   if [ "$FILE" == "format.pl" ] || [ "$FILE" == "format.sh" ]; then continue; fi
   ./format.pl $FILE
done
