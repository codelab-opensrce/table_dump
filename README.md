# TableDump

TableDump 0.1 CakePHP 2.x Plug-in
Copyright (c) Cake Codelab. (http://codelab.jp)

Licensed under The MIT License
Redistributions of files must retain the above copyright notice.

 @copyright     Copyright (c) Codelab. (http://codelab.jp)
 @link          http://codelab.jp
 @since         TableDump 0.1 CakePHP Plug-in
 @license       http://www.opensource.org/licenses/mit-license.php MIT License

This is CakePHP2.x Shell Plugin.
This Shell generates a CSV file form the database and imports the database from the CSV file.

CSV files are output to the APP\Config\Schema\export_{"model_name"}.csv .


[Usage]
cake tabledump [params] [model_name] {option1 option2 ....}

[Params]
import		csv to database.
export		database to csv.

[Options]
drop : table drop before import.
conection [database name]: database name. default is 'default'
force : reply in YES to all questions.
limit [num]: export records limit num.  default is 9999


