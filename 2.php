<?php

    error_reporting(E_ALL);

    //Реализовать функцию convertString($a, $b). Результат ее выполнение: если в строке $a содержится 2 и
    //более подстроки $b, то во втором месте заменить подстроку $b на инвертированную
    //подстроку.
    function convertString(string $a, string $b) {
        $resultA = $a;

        preg_match_all("/$b/", $a, $outArray, PREG_OFFSET_CAPTURE);

        //to change a substring
        if(count($outArray[0]) >= 2 ) {
            $positionSubstring = $outArray[0][1][1];
            $reversedB = strrev($b);
            $resultA = substr_replace($a, $reversedB, $positionSubstring, strlen($b));
        }

        return $resultA;
    }

    //Реализовать функцию mySortForKey($a, $b). $a – двумерный массив вида [['a'=>2,'b'=>1],['a'=>1,'b'=>3]],
    //$b – ключ вложенного массива. Результат ее выполнения: двумерном массива
    //$a отсортированный по возрастанию значений для ключа $b. В случае отсутствия ключа $b в
    //одном из вложенных массивов, выбросить ошибку класса Exception с индексом
    //неправильного массива.
    function mySortForKey(array $a, $b) {

        $startIndex = 0;//it is a start position of array for finding of a min value $b
        $minElement = null;//it stores a value and an index of current min element
        $lengthA = count($a);// it is a length of array $a

        for($i = 0; $i < $lengthA; $i++) {

            if($i == $startIndex) {

                $minElement = [
                    'value' => $a[$i],
                    'index' => $i
                ];

                //to find a min value $b from $innerI to the end
                for($innerI = $startIndex + 1; $innerI < $lengthA; $innerI++) {

                    $currentElement = $a[$innerI][$b];

                    if(!isset($currentElement)) throw new Exception("Error: the key \"$b\" is not exist in the array \$a in the index  $innerI!", 1);

                    //if the current element is less than $minElement
                    if($currentElement < $minElement['value'][$b]) {
                        //rewrite
                        $minElement = [
                            'value' => $a[$innerI],
                            'index' => $innerI
                        ];

                    }

                }

                //it swaps two elements of array
                $a[$minElement['index']] = $a[$startIndex];//to store in the min position index
                $a[$startIndex] = $minElement['value'];
                $startIndex++;//it goes to the next position

                //if it reached the end
                if($startIndex == ($lengthA - 1)) return $a;

            }

        }
    }

    //Реализовать функцию importXml($a). $a – путь к xml файлу (структура файла приведена ниже). 
    //Результат ее выполнения: прочитать файл $a и импортировать его в созданную БД.
    function importXml(string $a) {

        $doc = new DOMDocument('1.0', 'Windows-1251');
        $doc->load($a);

        //it prepares all data from the XML file for the database
        function getAllData(DOMDocument $doc): array {

            $products = $doc->getElementsByTagName('Товар');

            $productsInfo = [];//it will store all information about products
        
            //it does the recordes to the $productsInfo
            foreach($products as $product) {
        
                $prices = [];
                $properties = [];
                $categories = [];
        
                $children = $product->childNodes;
        
                foreach($children as $child) {
        
                    if($child->nodeName != '#text') {
        
                        switch($child->tagName) {
        
                            case 'Цена':
                                $type = $child->getAttribute('Тип');
                                $type = trim($type);
                                $value = $child->nodeValue;
                                $value = trim($child->nodeValue);
                                array_push($prices, ['type_of_price' => $type, 'price' => $value]);
                                break;
        
                            case 'Свойства':
                                $propChildren = $child->childNodes;
                                foreach($propChildren as $property) {
        
                                    if($property->nodeName != '#text') {
                                        $name = $property->tagName;
                                        $preparedPropertyString = "$name $property->nodeValue {$property->getAttribute('ЕдИзм')}";
                                        array_push($properties, $preparedPropertyString);
                                    };
        
                                }
                                break;
                            case 'Разделы':
                                $propChildren = $child->childNodes;
                                foreach($propChildren as $property) {
        
                                    if($property->nodeName != '#text') {
                                        $text = trim($property->nodeValue);
                                        $text = mb_convert_encoding($text, 'UTF-8');
                                        array_push($categories, trim($text));
                                    };
        
                                }
                                break;
                        }
        
                    };
        
                }
        
                array_push($productsInfo, 
                    [
                        'code' => $product->getAttribute('Код'),
                        'name' => $product->getAttribute('Название'),
                        'prices' => $prices,
                        'properties' => $properties,
                        'categories' => $categories
                    ]
                );
        
            }

            return $productsInfo;
        }

        //to import all data to a database
        function import(string $user, string $pass, array $data) {

            try {
                $connection = new PDO('mysql:host=localhost;dbname=test_samson', $user, $pass);
            } catch (PDOException $e) {
                echo 'Подключение не удалось: ' . $e->getMessage();
            }

            //to do all queries
            function doQueries(PDO $connection, array $data) {

                $prepared_category = $connection->prepare("INSERT INTO a_category (code, name) VALUES (?, ?)");
                $prepared_product = $connection->prepare("INSERT INTO a_product (code, name, a_category_id) VALUES (?, ?, ?)");
                $prepared_price = $connection->prepare("INSERT INTO a_price (a_product_id, type_of_price, price) VALUES (?, ?, ?)");
                $prepared_property = $connection->prepare("INSERT INTO a_property (a_product_id, value_of_property) VALUES (?, ?)");
                $prepared_subcategory = $connection->prepare("INSERT INTO a_subcategory (parent_id, child_id) VALUES (?, ?)");

                $prepared_category->bindParam(1, $code_category);
                $prepared_category->bindParam(2, $name_category);

                $prepared_product->bindParam(1, $code_product);
                $prepared_product->bindParam(2, $name_product);
                $prepared_product->bindParam(3, $a_category_id);

                $prepared_price->bindParam(1, $a_product_id);
                $prepared_price->bindParam(2, $type_of_price);
                $prepared_price->bindParam(3, $price);

                $prepared_property->bindParam(1, $a_product_id);
                $prepared_property->bindParam(2, $value_of_property);

                $prepared_subcategory->bindParam(1, $parent_id);
                $prepared_subcategory->bindParam(2, $child_id);



                //it gets a product id from a_product table
                function getProductId(PDO $connection, int $productCode): int {

                    $result = $connection->query("SELECT * FROM a_product WHERE code = $productCode");                       

                    $raw = $result->fetch(PDO::FETCH_ASSOC);
                    return (int) $raw['id'];

                }



                //it gets a category id from a_category table
                function getCategoryId(PDO $connection, string $nameCategory) {

                    $result = $connection->query("SELECT * FROM a_category WHERE name = '$nameCategory'");                       

                    $raw = $result->fetch(PDO::FETCH_ASSOC);
                    return $raw['id'];

                }



                //has a a_category table a catigory name or not
                function hasCategory(PDO $connection, string $name): bool {

                    $result = $connection->query("SELECT * FROM a_category WHERE name = '$name'");                       
        
                    if($result->fetch(PDO::FETCH_ASSOC)) return true;
                    return false;
                }



                //it gets a parent category id for a current category
                function getParentCategoryId(int $currentCategoryId) {
                    return --$currentCategoryId;
                }


                foreach($data as $propertiesProduct) {

                    for($i = 0; $i < count($propertiesProduct['categories']); $i++) {

                        $code_category = null;
                        $name_category = $propertiesProduct['categories'][$i];

                        //if a_category table has not a catigory name then to do the record
                        if(!hasCategory($connection, $name_category)) {

                            $prepared_category->execute();//to do the record

                            //is it a child catigory?
                            if($i > 0) {
                                //to do a record to the a_subcategory table
                                $child_id = getCategoryId($connection, $name_category);
                                $parent_id = $child_id - 1;

                                $prepared_subcategory->execute();
                            }
                        
                        }
                            
                    }

                    $code_product = $propertiesProduct['code'];
                    $name_product = $propertiesProduct['name'];
                    $a_category_id = getCategoryId($connection, $name_category);

                    $prepared_product->execute();
                    
                    for($i = 0; $i < count($propertiesProduct['prices']); $i++) {
        
                        //it prepared all data to a query
                        $code_product = (int) $code_product;
        
                        $a_product_id = getProductId($connection, $code_product);
                        $type_of_price = $propertiesProduct['prices'][$i]['type_of_price'];
                        $price = $propertiesProduct['prices'][$i]['price'];
        
                        $prepared_price->execute();

                    }

                    foreach($propertiesProduct['properties'] as $property) {

                        $value_of_property = $property;

                        $prepared_property->execute();

                    }

                }
                
            }

            doQueries($connection, $data);
        }

        $productsInfo = getAllData($doc);
        import('root', 'root', $productsInfo);

    }

    //Реализовать функцию exportXml($a, $b). $a – путь к xml файлу вида (структура файла приведена ниже), 
    //$b – код рубрики. Результат ее выполнения: выбрать из БД товары (и их характеристики, 
    //необходимые для формирования файла) выходящие в рубрику $b или в любую из всех вложенных в нее рубрик, 
    //сохранить результат в файл $a.
    function exportXml($a, $b) {

        //it finds all products id from the a_product table
        function findAllProductsId(int $categoryCode): array {

            //to connect to the database
            function connect(string $user, string $pass): PDO {
                try {
                    $connection = new PDO('mysql:host=localhost;dbname=test_samson', $user, $pass);
                } catch (PDOException $e) {
                    echo 'Подключение не удалось: ' . $e->getMessage();
                }
                return $connection;
            }



            //to perform a query. It returns all finded raws of a_product table with all id of products
            function performsQuery(PDO $connection, int $categoryCode): array {
                $allProductsId = [];
                $stmt = $connection->prepare("SELECT * FROM a_product WHERE a_category_id = ?");
                $stmt->bindParam(1, $categoryCode);

                $stmt->execute();

                while ($raw = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    //to find products Id
                    array_push($allProductsId, $raw['id']);
                };
                
                $connection = null;

                return $allProductsId;
            }



            $connection = connect('root', 'root');

            return performsQuery($connection, $categoryCode);

        }

        //it sets a $product[prices]
        function setPrices($prices) {
            //it splits an array into chunks on 2 elements
            if(count($prices) > 2) return array_chunk($prices, 2);
            return $prices;
        }

        //to find a code and a name from a_product table
        function findFromProduct(PDO $connection, int $productId): array {
            $product = [];//[0 => 107, 1 => 'Бумага А4']

            $stmt = $connection->prepare("SELECT * FROM a_product WHERE id = ?");
            $stmt->bindParam(1, $productId);

            $stmt->execute();

            while ($raw = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //to find a code and a name
                array_push($product, $raw['code'], $raw['name']);
            };

            return $product;
        }

        //to find all data about prices from a_price table
        function findFromPrice(PDO $connection, int $productId): array {
            $product = [];//[0 => 'Москва', 1 => 12.5, 2 => 'Базовая', ...]

            $stmt = $connection->prepare("SELECT * FROM a_price WHERE a_product_id = ?");
            $stmt->bindParam(1, $productId);

            $stmt->execute();

            while ($raw = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //to find a type_of_price and a price
                array_push($product, $raw['type_of_price'], $raw['price']);
            };

            return $product;
        }

        //to find all properties about product from a_product table
        function findFromProperty(PDO $connection, int $productId): array {
            $product = [];//[0 => 'Плотность 100', 1 => Белизна 150 %, ...]

            $stmt = $connection->prepare("SELECT * FROM a_property WHERE a_product_id = ?");
            $stmt->bindParam(1, $productId);

            $stmt->execute();

            while ($raw = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //to find a value_of_property
                array_push($product, $raw['value_of_property']);
            };

            return $product;
        }

        //to find all categories about product from a_category table
        function findFromCategory(PDO $connection, int $subCategoryCode): array {
            $product = [];//[0 => 'Принтеры', 1 => 'МФУ', ...]

            $stmtCat = $connection->prepare("SELECT * FROM a_category WHERE id = ?");
            $stmtSubcat = $connection->prepare("SELECT * FROM a_subcategory WHERE child_id = ?");

            $stmtCat->bindParam(1, $categoryCode);
            $stmtSubcat->bindParam(1, $subCategoryCode);

            //$stmtCat->execute();
            $stmtSubcat->execute();


            while ($raw = $stmtSubcat->fetch(PDO::FETCH_ASSOC)) {
                //to find an id from a subcategories
                $categoryCode = $raw['child_id'];
                $categoryCodeParentId = $raw['parent_id'];
                
                $stmtCat->execute();

                $categoryCode = $categoryCodeParentId;//it rewrites a $categoryCode for the parent id 

                $raw = $stmtCat->fetch(PDO::FETCH_ASSOC);
                
                array_push($product, $raw['name']);
            };

            $stmtCat->execute();

            while ($raw = $stmtCat->fetch(PDO::FETCH_ASSOC)) {
                //to find a name
                array_push($product, $raw['name']);
            };


            return $product;
        }

        //to crate an array with all properties of product and to get it
        //$prices = [0 => 'Москва', 1 => 100.90, 2 => 'Базовая', 3 => 20.50 ...]
        function getCreatedProduct(int $code, string $name, array $prices, array $propertes, array $categories): array {

            $product = [
                'code' => $code,
                'name' => $name,
                'prices' => setPrices($prices),
                'propertes' => $propertes,
                'categories' => $categories
            ];

            return $product;
        }

        //it fills an array of products
        function fillProduct(array $productsId, int $categoryCode, callable $getCreatedProduct): array {

            $connection = connect('root', 'root');

            $allProductsData = [];

            foreach($productsId as $productId) {
                $data_a_product_table = findFromProduct($connection, $productId);
                $prices = findFromPrice($connection, $productId);
                $properties = findFromProperty($connection, $productId);
                $categories = findFromCategory($connection, $categoryCode);

                $productData = $getCreatedProduct(
                    $data_a_product_table[0],
                    $data_a_product_table[1],
                    $prices,
                    $properties,
                    $categories
                );

                array_push($allProductsData, $productData);
            }

            $connection = null;

            return $allProductsData;
        }


        $productsId = findAllProductsId($b);
        $allProducts = fillProduct($productsId, $b, 'getCreatedProduct');

        //it creates an XML document
        function createXMLbody(DOMDocument $doc, int $code, string $name, array $prices, array $properties, array $categories) {

            $products = $doc->createElement('Товары');
            $products = $doc->appendChild($products);

            $productN = $doc->createElement('Товар');
            $productN_Code = $doc->createAttribute('Код');
            $productN_Code->value = $code;
            $productN_Name = $doc->createAttribute('Название');
            $productN_Name->value = $name;
            $productN = $products->appendChild($productN);
            $productN->appendChild($productN_Code);
            $productN->appendChild($productN_Name);

            foreach($prices as $price) {
                $price_N = $doc->createElement('Цена', $price[1]);
                $price_N_type = $doc->createAttribute('Тип');
                $price_N_type->value = $price[0];
                $price_N = $products->appendChild($price_N);
                $price_N->appendChild($price_N_type);
            }

            $propertiesN = $doc->createElement('Свойства');
            $propertiesN = $products->appendChild($propertiesN);

            //it reads a content of a a_property table (from a string)
            foreach($properties as $property) {
                //it finds a tag name and a value from a string
                $resultProperties = explode(' ', $property);

                $prop = $doc->createElement(trim($resultProperties[0]), trim($resultProperties[1]));
                if(count($resultProperties) > 2 && !empty(trim($resultProperties[2]))) {
                    $prop_attr = $doc->createAttribute('ЕдИзм');
                    $prop_attr->value = trim($resultProperties[2]);
                    $prop->appendChild($prop_attr);
                }
                $prop = $propertiesN->appendChild($prop);
            }

            $categories_N = $doc->createElement('Разделы');
            $categories_N = $products->appendChild($categories_N);

            krsort($categories);

            foreach($categories as $category) {
                $cat = $doc->createElement('Раздел', $category);
                $cat = $categories_N->appendChild($cat);
            }

        }


        //it save a content to XML file
        function saveToXML(array $allProducts, callable $createXMLbody, string $pathToFile) {

            $doc = new DOMDocument('1.0', 'Windows-1251');

            $doc->formatOutput = true;

            foreach($allProducts as $product) {
                $createXMLbody($doc, $product['code'], $product['name'], $product['prices'], $product['propertes'], $product['categories']);
            }

            $doc->save($pathToFile);
        }

        saveToXML($allProducts, 'createXMLbody', "test.xml");
    }

    $parameterFirstMySortForKey = [
        [
            'a' => 3,
            'b' => 1,
            'c' => 4
        ],
        [
            'a' => 43,
            'b' => 14,
            'c' => 4
        ],
        [
            'a' => 1,
            'b' => 5,
            'c' => 40
        ]
    ];

    $resultMySortForKey = mySortForKey($parameterFirstMySortForKey, 'b');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>Реализовать функцию convertString($a, $b). Результат ее выполнение: если в строке $a содержится 2 и
    более подстроки $b, то во втором месте заменить подстроку $b на инвертированную подстроку.</h2>
    <pre>
        <?= convertString('this is my cat', 'is'); ?>
    </pre>
    <h2>Функия mySortForKey($a, $b). $a – двумерный массив вида [['a'=>2,'b'=>1],['a'=>1,'b'=>3]],
    $b – ключ вложенного массива. Результат ее выполнения: двумерном массива
    $a отсортированный по возрастанию значений для ключа $b. В случае отсутствия ключа $b в
    одном из вложенных массивов, выбросить ошибку класса Exception с индексом
    неправильного массива</h2>
    <pre>
        <?= print_r(mySortForKey([['a'=>2,'b'=>1],['a'=>1,'b'=>3]], 'a')); ?>
    </pre>
    <h2>Реализовать функцию importXml($a). $a – путь к xml файлу (структура файла приведена ниже). 
    Результат ее выполнения: прочитать файл $a и импортировать его в созданную БД.</h2>
    <pre>
        <?= 'hi!'//importXml('products.xml')?>
    </pre>
    <h2>Реализовать функцию exportXml($a, $b). $a – путь к xml файлу вида (структура файла приведена ниже), 
    $b – код рубрики. Результат ее выполнения: выбрать из БД товары (и их характеристики, необходимые для 
    формирования файла) выходящие в рубрику $b или в любую из всех вложенных в нее рубрик, сохранить результат 
    в файл $a.</h2>
    <pre>
        <?= exportXml('test.xml', 174)?>
    </pre>
</body>
</html>