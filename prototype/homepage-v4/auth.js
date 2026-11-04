/* FORKEL — демонстрационная «авторизация» (mock). Сервера, паролей и настоящих учётных записей нет: пароль не проверяется
   и не сохраняется. В sessionStorage этой вкладки (если доступен) лежит только имя и email — до закрытия вкладки. */
(function () {
  'use strict';
  var KEY = 'fk-demo-auth-v1';
  var memory = null;
  function user() {
    try {
      var raw = window.sessionStorage.getItem(KEY);
      if (raw) { var u = JSON.parse(raw); if (u && typeof u.email === 'string') return u; }
      return null;
    } catch (e) { return memory; }
  }
  function login(u) {
    memory = u;
    try { window.sessionStorage.setItem(KEY, JSON.stringify(u)); } catch (e) { /* только в памяти страницы */ }
    paint();
  }
  function logout() {
    memory = null;
    try { window.sessionStorage.removeItem(KEY); } catch (e) { /* нечего удалять */ }
    paint();
  }
  function paint() {
    var u = user();
    [].forEach.call(document.querySelectorAll('[data-account]'), function (a) {
      a.classList.toggle('is-in', !!u);
      var s = a.querySelector('.fk-vh');
      if (s) s.textContent = u ? 'Личный кабинет (демо-вход выполнен)' : 'Личный кабинет';
    });
  }
  window.FKAuth = { user: user, login: login, logout: logout };
  paint();
})();
