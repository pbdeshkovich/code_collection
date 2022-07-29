/**
 * ПРИМЕР JS --- копирование операций перевода прибыли с фриланса в таблицу личных финансов
*/

// триггер запускается по событию изменения ячеек в Google Таблице, стартует обработчик привязанный к конкретному листу
function onEdit(event) {
  var ss = event.source.getActiveSheet();
  switch (ss.getName()) {
    case 'ТРАНЗАКЦИИ':
      var transactionsLists = new TransactionsList();
      transactionsLists._startTransactionsListCoreOnEvent(ss, event);
      break;
    default:break;
  }

// В классе собирается все, что касается обработки конкретного листа таблицы
// Подобную реализацию выбрал для разделения логики по листам (на листах бизнес-логика разная - зависит от хранящихся данных)
  class TransactionsList {

  constructor() {
    this.ui = new UserInterface();
  }
  
  _startTransactionsListCoreOnEvent(ss, event) {
    this.eventData = { ss: ss, event: event };
    var columt_edited = event.source.getActiveRange().getColumn();
    switch (columt_edited) {
      case 1:
        this.createProfitTransactionToMyFinances_core();
        break;
      default: break;
    }
  }

  createProfitTransactionToMyFinances_core() {
    var transaction = this.eventData.ss.getRange(this.eventData.event.source.getActiveRange().getRow(), 1, 1, 6).getValues()[0];
    if (!this.checkTransactionBeforeCopyToMyFinance(transaction)) {
      this.ui._sendUIAlert('ОШИБКА: транзакция не будет скопирована', 'Заполнены не все поля. Дату проводки заполняй последней, ее заполнение инициирует копирование');
      this.eventData.ss.getRange(this.eventData.event.source.getActiveRange().getRow(), 1).setValue('');
      return false;
    }
    // Здесь вызывается API метод из библиотеки другой таблицы, которая к себе сохраняет данные о платеже
    // ее код раскрыть не могу в целях сохранения личной конфиденциальности
    console.log(MyFinances.pasteNewProfitTransactionFromMyFreelance(transaction[0], transaction[2]));
  }

  checkTransactionBeforeCopyToMyFinance(transaction) {
    for (let i = 0; i < 6; i++) {
      if (transaction[i] == '') return false;
    }
    return true;
  }

}

// вывод UI Alert в таблице вынес в отдельный класс, чтобы получить возможность удобного расширения на будущее
// можно обратиться к UI Google Sheets в любом файле скрипта (так разрешает Google)
class UserInterface {

  constructor() {
    this.ui = SpreadsheetApp.getUi();
  }

  _sendUIAlert(title, message, buttons = null) {
    var response = null;
    switch (buttons) {
      case null: response = this.ui.alert(title, message, this.ui.ButtonSet.OK); break;
      case 'YES_NO': response = this.ui.alert(title, message, this.ui.ButtonSet.YES_NO); break;
      default: break;
    }
    switch (response) {
      case this.ui.Button.YES: response = true; break;
      default: response = null; break;
    }
    return response;
  }

}