/**
 * translation for: xajax v.x.x
 * @version: 1.0.0
 * @author: mic <info@joomx.com>
 * @copyright xajax project
 * @license GNU/GPL
 * @package xajax x.x.x
 * @since v.x.x.x
 * save as UTF-8
 */

if ('undefined' != typeof xajax.debug) {
	/*
		Array: text
	*/
	xajax.debug.text = [];
	xajax.debug.text[100] = 'ПРЕДУПРЕЖДЕНИЕ: ';
	xajax.debug.text[101] = 'ГРЕШКА: ';
	xajax.debug.text[102] = 'XAJAX ДЕБЪГ СЪОБЩЕНИЕ:\n';
	xajax.debug.text[103] = '...\n[ДЪЛЪГ ОТГОВОР]\n...';
	xajax.debug.text[104] = 'ИЗПРАЩАНЕ НА ЗАЯВКИ';
	xajax.debug.text[105] = 'ИЗПРАТЕНИ [';
	xajax.debug.text[106] = ' байта]';
	xajax.debug.text[107] = 'ИЗВИКВАНЕ: ';
	xajax.debug.text[108] = 'Адрес: ';
	xajax.debug.text[109] = 'ИНИЦИАЛИЗИРАНЕ НА ЗАЯВКАТА';
	xajax.debug.text[110] = 'ОБРАБОТВАНЕ НА ПАРАМЕТРИТЕ [';
	xajax.debug.text[111] = ']';
	xajax.debug.text[112] = 'НЯМА ПАРАМЕТРИ ЗА ОБРАБОТВАНЕ';
	xajax.debug.text[113] = 'ПОДГОТВЯВАНЕ НА ЗАЯВКАТА';
	xajax.debug.text[114] = 'СТАРТИРАНЕ НА XAJAX ПОВИКВАНЕТО (остаряло: вместо това използвай xajax.request)';
	xajax.debug.text[115] = 'СТАРТИРАНЕ НА XAJAX ЗАЯВКАТА';
	xajax.debug.text[116] = 'Няма регистрирани функции, които да обработят заявката ви на сървъра!\n';
	xajax.debug.text[117] = '.\nПровери за съобщения за грешки на сървъра.';
	xajax.debug.text[118] = 'ПОЛУЧЕНИ [статус: ';
	xajax.debug.text[119] = ', размер: ';
	xajax.debug.text[120] = ' байта, време: ';
	xajax.debug.text[121] = 'мсек]:\n';
	xajax.debug.text[122] = 'Сървъра върна следния HTTP статус: ';
	xajax.debug.text[123] = '\nПОЛУЧЕНИ:\n';
	xajax.debug.text[124] = 'Сървъра върна пренасочване към:<br />';
	xajax.debug.text[125] = 'ГОТОВО [';
	xajax.debug.text[126] = 'мсек]';
	xajax.debug.text[127] = 'ИНИЦИАЛИЗИРАНЕ НА ОБЕКТА НА ЗАЯВКАТА';
	 
	xajax.debug.exceptions = [];
	xajax.debug.exceptions[10001] = 'Невалиден XML отговор: Отговора съдържа непознат таг: {data}.';
	xajax.debug.exceptions[10002] = 'GetRequestObject: Няма XMLHttpRequest, xajax е изключен.';
	xajax.debug.exceptions[10003] = 'Препълване на опашката: Обекта не може да бъде сложен на опашката, защото тя е пълна.';
	xajax.debug.exceptions[10004] = 'Невалиден XML отговор: Отговора съдържа неочакван таг или текст: {data}.';
	xajax.debug.exceptions[10005] = 'Невалиден адрес: Невалиден или липсващ адрес; автоматичното откриване неуспешнп; please specify a one explicitly.';
	xajax.debug.exceptions[10006] = 'Невалидна команда в отговора: Получена беше невалидна команда като отговор.';
	xajax.debug.exceptions[10007] = 'Невалидна команда в отговора: Командата [{data}] е непозната.';
	xajax.debug.exceptions[10008] = 'Елемент с ID [{data}] не беше намерен в документа.';
	xajax.debug.exceptions[10009] = 'Невалидна заявка: Параметъра с името на функцията липсва.';
	xajax.debug.exceptions[10010] = 'Невалидна заявка: Липсва обекта на функцията.';
}
       
if ('undefined' != typeof xajax.config) {
  if ('undefined' != typeof xajax.config.status) {
    /*
      Object: update
    */
    xajax.config.status.update = function() {
      return {
        onRequest: function() {
          window.status = 'Изпращане на заявка...';
        },
        onWaiting: function() {
          window.status = 'Изчакване на отговор...';
        },
        onProcessing: function() {
          window.status = 'Изпълнение...';
        },
        onComplete: function() {
          window.status = 'Готово.';
        }
      }
    }
  }
}