<?php
/**
 * Код компонента с его вызовом собрал в один файл для знакомства с кодом, при этом не раскрывать прилегающего кода проекта
 *
 * Сайт, для которого разрабатывался - https://belwooddoors.ru/
 *
 * ИСХОДНАЯ ЗАДАЧА:
 * Разработать компонент для вывода микроразметки Event между тегами <head>...</head>
 * Данные для микроразметки берутся из акционного баннера под меню, который реализован компонентом и включается по необходимости под главным меню.
 * Микроразметка Event выводится только для catalog.section
 *
 * РЕАЛИЗАЦИЯ через отложенные функции, так как компонент баннера хранит все данные в JS файле, внутри себя. После каждого изменения перезаписывает указанный файл, где собрана логика JS и данные.
 * Компонент баннера реализовывался другим разработчиком давно.
 */


/*
 * include/tags_head.php --- сюда вынесено формирование шапки сайта шаблона
 * вызываю свой компонент
 */
if ($APPLICATION->GetCurPage() == '/catalog/mezhkomnatnye_dveri/') { // знаю, что топорная проверка --- так построена логика на проекте
    $APPLICATION->IncludeComponent(
        "medialine:micromarking.event",
        ".default",
        Array( ),
        false
    );
}


/**
 * в components.php отвечающего за баннер с акциями добавил одну строку (строка 37) в представленный участок
 */
$this->arResult["DATE_BEFORE"] = date('Y-m-d',strtotime($arParams["MY_DATA"]));

if ($arParams["ACTIVE"] == "Y" &&
    (strtotime($this->arResult["DATE_BEFORE"]) > strtotime(date("Y-m-d ")))
) {
    $GLOBALS['promotionalHeadBanner'] = array_merge($this->arResult, $this->arParams); // сохр. для использования в микроразметке event
    $this->IncludeComponentTemplate($this->arResult["TEMPLATE"]);
}

/**
 * Свой компонент положил в local/components/[название компании работодателя]/micromarking.event --- далее считаем корнем компонента
 */

/**
 * deskription.php
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => 'Микроразметка Event',
    "DESCRIPTION" => 'Выводит микроразметку event на страницах catalog.section, данные берет с баннера акций',
);


/**
 * component.php
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//$this->IncludeComponentTemplate();

$APPLICATION->AddBufferContent(function() {
    global $APPLICATION;

    if (!isset($GLOBALS['promotionalHeadBanner']) || (count($GLOBALS['promotionalHeadBanner']) == 0)) return;

    ob_start();
    $this->IncludeComponentTemplate();
    $template = ob_get_contents();
    ob_end_clean();

    return $template;
});
?>

<?php
/**
 * templates/.default/template.php
 * дальше разделил на разные PHP-блоки, чтобы представить HTML и PHP в этом файле
 */
?>

<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<!-- Микроразметка Event -->
<script type="application/ld+json">
[
    {
        "@context":"http://schema.org",
        "@type":"Event",
        "name":"<?=$GLOBALS['promotionalHeadBanner']['MAIN_TEXT']?>",
        "description":"<?=$GLOBALS['promotionalHeadBanner']['MAIN_TEXT'].' '.$GLOBALS['promotionalHeadBanner']['ADDITIONAL_TEXT']?>",
        "url":"<?=$GLOBALS['promotionalHeadBanner']['LINK_FOR_PROMOTIONAL']?>",
        "endDate":"<?=$GLOBALS['promotionalHeadBanner']['DATE_BEFORE']?>",
        "location":{
            "@type":"Place",
            "name":"Межкомнатные двери",
            "address": {
                "@type" : "PostalAddress",
                "addressLocality" : "Минск",
                "streetAddress" : "ул. Промышленная, 23",
                "postalCode" : "220075"
            }
        }
    }
]
</script>