<?php

error_reporting(0);


//Реализовать функцию findSimple ($a, $b). $a и $b – целые положительные числа. 
//Результат ее выполнение: массив простых чисел от $a до $b.
function findSimple(int $a, int $b): array {
    $arr = [];

    if($a < 1 || $b < 1) {
        throw new Exception('в функции "findSimple(int $a, int $b)" $a и $b должны быть целыми положительными числами');
    }

    for(; $a <= $b; $a++) {
        $arr[] = $a;
    }

    return $arr;
}


//$a – массив положительных чисел, количество элементов кратно 3.
//Результат ее выполнение: двумерный массив (массив состоящий из ассоциативных массива с ключами a, b, c).
//Пример для входных массива [1, 2, 3, 4, 5, 6] результат [[‘a’=>1,’b’=>2,’с’=>3],[‘a’=>4,’b’=>5 ,’c’=>6]].
function createTrapeze(array $a): array {
    $length = count($a);

    if($length % 3) {
        throw new Exception('в функции "createTrapeze(array $a)" количество элементов $a должно быть кратно 3!');
    }

    $resultArray = [];
    $resultLength = $length/3; // the length of returned array
    $currentPosition = 0; //this is current position in the $a array

    //it will fill the returned array
    for($i = 0; $i < $resultLength; $i++) {
        $resultArray[] = [
            'a' => $a[$currentPosition++],
            'b' => $a[$currentPosition++],
            'c' => $a[$currentPosition++]
        ];
    }

    return $resultArray;
}


/*$a – массив результата выполнения функции createTrapeze().
Результат ее выполнение: в исходный массив для каждой тройки чисел добавляется дополнительный ключ s, 
содержащий результат расчета площади трапеции со сторонами a и b, и высотой c.*/
function squareTrapeze(array $a): array {
    for($i = 0; $i < count($a); $i++) {

        $heightC = $a[$i]['c'];
        $sideA = $a[$i]['a'];
        $sideB = $a[$i]['b'];
        $squareS = 1/2 * $heightC * ($sideA + $sideB);

        $a[$i]['s'] = $squareS;
    }

    return $a;
}


//$a – массив результата выполнения функции squareTrapeze(), $b – максимальная площадь.
//Результат ее выполнение: массив размеров трапеции с максимальной площадью, но меньше или равной $b.
//it returns null or array
function getSizeForLimit(array $a, float $b) {
    $position = null;//it is a position of trapaee in $a array (with max square)
    $currentMaxSquare = 0;

    for($i = 0; $i < count($a); $i++) {
        $currentSquare = $a[$i]['s'];

        if($currentSquare <= $b && $currentSquare > $currentMaxSquare) {
            $currentMaxSquare = $currentSquare;
            $position = $i;
        }

    }

    if(isset($position)) return $a[$position];

    return null;
}


//$a – массив чисел. Результат ее выполнения: минимальное числа в массиве (не используя функцию min, 
//ключи массив может быть ассоциативный).
function getMin(array $a) {
    $currentMinValue = null;

    foreach($a as $value) {

        if(!isset($currentMinValue)) {
            $currentMinValue = $value;
        } else {
            if($value < $currentMinValue) $currentMinValue = $value;
        }

    }

    return $currentMinValue;
}


//$a – массив результата выполнения функции squareTrapeze(). 
//Результат ее выполнение: вывод таблицы с размерами трапеций, строки с нечетной площадью трапеции 
//отметить любым способом.
function printTrapeze($a) {
    $contentOfTable = "";

    foreach ($a as $parametorOfTrapeze) {
        //if square is an odd number
        $oddNumber = floor($parametorOfTrapeze['s']);

        if($oddNumber % 2) {
            //to paint the raw (<tr>)
            $contentOfTable .= "<tr style='background-color: yellow'><td>$parametorOfTrapeze[a]</td>
            <td>$parametorOfTrapeze[b]</td>
            <td>$parametorOfTrapeze[c]</td>
            <td>$parametorOfTrapeze[s]</td></tr>";
        } else {
            $contentOfTable .= "<tr><td>$parametorOfTrapeze[a]</td>
            <td>$parametorOfTrapeze[b]</td>
            <td>$parametorOfTrapeze[c]</td>
            <td>$parametorOfTrapeze[s]</td></tr>";
        }

    }

    $resultContent = "<table>
        <tr>
            <th>Основание трапеции 'a'</th>
            <th>Основание трапеции 'b'</th>
            <th>Высота трапеции 'c'</th>
            <th>Площадь трапеции 's'</th>
        </tr>
        $contentOfTable
    </table>";

    return $resultContent;
}


$simpleArr = findSimple(1, 12);
$trapezes = createTrapeze($simpleArr);
$squares = squareTrapeze($trapezes);
$maxTrapeze = getSizeForLimit($squares, 40);
$minValue = getMin(['fs' => 5, 16, 3, 8, 42, 100]);



//Реализовать абстрактный класс BaseMath содержащий 3 метода: exp1($a, $b, $c) и exp2($a, $b, $c),getValue(). 
//Метода exp1 реализует расчет по формуле a*(b^c). Метода exp2 реализует расчет по формуле (a/b)^c. 
//Метод getValue() возвращает результат расчета класса наследника.
abstract class BaseMath
{
    public function exp1($a, $b, $c) {
        return $a * ($b ^ $c);
    }


    public function exp2($a, $b, $c) {
        return ($a / $b) ^ $c;
    }

    abstract protected function getValue();
}



//Реализовать класс F1 наследующий методы BaseMath, содержащий конструктор с параметрами ($a, $b, $c) и метод getValue(). 
//Класс реализует расчет по формуле f=(a*(b^c)+(((a/c)^b)%3)^min(a,b,c)).
class F1 extends BaseMath
{
    public $a;
    public $b;
    public $c;

    public function __construct($a, $b, $c) {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function getValue() {
        $f = ($this->a * ($this->b ^ $this->c) + ((($this->a / $this->c) ^ $this->b) % 3) ^ min($this->a, $this->b, $this->c));
        return $f;
    }

}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h3>Реализовать функцию findSimple ($a, $b). $a и $b – целые положительные числа. Результат ее выполнение: массив простых чисел от $a до $b.</h3>
    <pre><?= var_dump($simpleArr); ?></pre>

    <h3>Реализовать функцию createTrapeze($a). $a – массив положительных чисел, количество элементов кратно 3. Результат ее выполнение: двумерный массив (массив состоящий из ассоциативных массива с ключами a, b, c). Пример для входных массива [1, 2, 3, 4, 5, 6] результат [[‘a’=>1,’b’=>2,’с’=>3],[‘a’=>4,’b’=>5 ,’c’=>6]].</h3>
    <pre><?= var_dump($trapezes); ?></pre>

    <h3>Реализовать функцию squareTrapeze($a). $a – массив результата выполнения функции createTrapeze(). Результат ее выполнение: в исходный массив для каждой тройки чисел добавляется дополнительный ключ s, содержащий результат расчета площади трапеции со сторонами a и b, и высотой c.</h3>
    <pre><?= var_dump($squares); ?></pre>

    <h3>Реализовать функцию getSizeForLimit($a, $b). $a – массив результата выполнения функции squareTrapeze(), $b – максимальная площадь. Результат ее выполнение: массив размеров трапеции с максимальной площадью, но меньше или равной $b.</h3>
    <pre><?= var_dump($maxTrapeze); ?></pre>

    <h3>Реализовать функцию getMin($a). $a – массив чисел. Результат ее выполнения: минимальное числа в массиве (не используя функцию min, ключи массив может быть ассоциативный).</h3>
    <pre><?= var_dump($minValue); ?></pre>

    <h3>Реализовать функцию printTrapeze($a). $a – массив результата выполнения функции squareTrapeze(). Результат ее выполнение: вывод таблицы с размерами трапеций, строки с нечетной площадью трапеции отметить любым способом.</h3>
    <pre><?= printTrapeze($squares); ?></pre>
</body>
</html>