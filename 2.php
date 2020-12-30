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
        <?= var_dump($resultMySortForKey); ?>
    </pre>
</body>
</html>