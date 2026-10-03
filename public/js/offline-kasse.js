'use strict';

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

function _objectWithoutProperties(obj, keys) { var target = {}; for (var i in obj) { if (keys.indexOf(i) >= 0) continue; if (!Object.prototype.hasOwnProperty.call(obj, i)) continue; target[i] = obj[i]; } return target; }

(function () {
    var DB_NAME = 'klamottenboerse-kasse';
    var STORE_NAME = 'pending_sales';
    var DEVICE_ID = Date.now() + '-' + Math.random().toString(16).slice(2);

    function isOfflineRequestError(error) {
        if (!error) {
            return false;
        }

        return error instanceof TypeError || error.name === 'AbortError';
    }

    function openDatabase() {
        return new Promise(function (resolve, reject) {
            if (!('indexedDB' in window)) {
                reject(new Error('IndexedDB is not supported in this browser.'));
                return;
            }

            var request = window.indexedDB.open(DB_NAME, 1);

            request.onupgradeneeded = function (event) {
                var db = event.target.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
                }
            };

            request.onsuccess = function () {
                resolve(request.result);
            };

            request.onerror = function () {
                reject(request.error || new Error('Unable to open IndexedDB.'));
            };
        });
    }

    function addQueuedSale(salePayload) {
        return openDatabase().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction(STORE_NAME, 'readwrite');
                var store = tx.objectStore(STORE_NAME);
                var request = store.add(_extends({}, salePayload, {
                    device_id: DEVICE_ID,
                    created_at: new Date().toISOString(),
                    synced: false
                }));

                request.onsuccess = function () {
                    resolve(request.result);
                };

                request.onerror = function () {
                    reject(request.error || new Error('Unable to write queued sale.'));
                };
            });
        });
    }

    function getQueuedSales() {
        return openDatabase().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction(STORE_NAME, 'readonly');
                var store = tx.objectStore(STORE_NAME);
                var request = store.getAll();

                request.onsuccess = function () {
                    resolve(request.result || []);
                };

                request.onerror = function () {
                    reject(request.error || new Error('Unable to read queued sales.'));
                };
            });
        });
    }

    function removeQueuedSales(ids) {
        return openDatabase().then(function (db) {
            return Promise.all(ids.map(function (id) {
                return new Promise(function (resolve, reject) {
                    var tx = db.transaction(STORE_NAME, 'readwrite');
                    var store = tx.objectStore(STORE_NAME);
                    var request = store.delete(id);

                    request.onsuccess = function () {
                        resolve();
                    };

                    request.onerror = function () {
                        reject(request.error || new Error('Unable to remove queued sales.'));
                    };
                });
            }));
        });
    }

    function syncQueuedSales() {
        var csrfTokenElement = document.head.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfTokenElement ? csrfTokenElement.content : null;
        if (!csrfToken) {
            return Promise.resolve(false);
        }

        return getQueuedSales().then(function (sales) {
            if (!sales.length) {
                return true;
            }

            var payload = sales.map(function (_ref) {
                var id = _ref.id,
                    device_id = _ref.device_id,
                    created_at = _ref.created_at,
                    synced = _ref.synced,
                    sale = _objectWithoutProperties(_ref, ['id', 'device_id', 'created_at', 'synced']);

                return _extends({}, sale, {
                    device_id: device_id,
                    created_at: created_at
                });
            });

            return fetch('/kasse/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Device-Id': DEVICE_ID
                },
                credentials: 'same-origin',
                body: JSON.stringify({ sales: payload })
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Offline sync failed');
                }

                return response.json().then(function (result) {
                    if (result.ok) {
                        var ids = sales.map(function (sale) {
                            return sale.id;
                        });
                        return removeQueuedSales(ids).then(function () {
                            return true;
                        });
                    }

                    return false;
                });
            });
        });
    }

    function submitSale(form) {
        var formData = new FormData(form);

        return fetch(form.action, {
            method: form.method || 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Online sale submit failed');
            }

            return response;
        });
    }

    function serializeForm(form) {
        var formData = new FormData(form);
        var data = {};

        var _iteratorNormalCompletion = true;
        var _didIteratorError = false;
        var _iteratorError = undefined;

        try {
            for (var _iterator = formData.entries()[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                var _ref2 = _step.value;

                var _ref3 = _slicedToArray(_ref2, 2);

                var key = _ref3[0];
                var value = _ref3[1];

                if (key === 'submit' || key === '_token') {
                    continue;
                }

                data[key] = value;
            }
        } catch (err) {
            _didIteratorError = true;
            _iteratorError = err;
        } finally {
            try {
                if (!_iteratorNormalCompletion && _iterator.return) {
                    _iterator.return();
                }
            } finally {
                if (_didIteratorError) {
                    throw _iteratorError;
                }
            }
        }

        return data;
    }

    function setStatus(message, isWarning) {
        var showFeedback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

        var element = document.getElementById('offline-status') || (showFeedback ? document.getElementById('offline-sale-feedback') : null);
        if (!element) {
            return;
        }

        element.textContent = message;
        element.hidden = false;
        element.className = element.id === 'offline-sale-feedback' ? isWarning ? 'alert alert-warning' : 'alert alert-success' : isWarning ? 'text-amber-600' : 'text-emerald-600';
    }

    function attachFormListener() {
        var form = document.querySelector('form[name="kasse"]');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var payload = serializeForm(form);

            submitSale(form).then(function () {
                window.location.assign('/kasse');
            }).catch(function (error) {
                if (!navigator.onLine && !isOfflineRequestError(error)) {
                    throw error;
                }

                return addQueuedSale(payload).then(function () {
                    setStatus('Offline: Verkauf lokal gespeichert und wird synchronisiert.', true, true);
                    form.reset();
                }).catch(function () {
                    setStatus('Offline: Speicherung fehlgeschlagen, bitte erneut versuchen.', true, true);
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var syncButton = document.getElementById('sync-offline-sales');

        if (navigator.onLine) {
            setStatus('Online: Synchronisation aktiv.', false);
            syncQueuedSales().catch(function () {
                return setStatus('Offline-Puffer konnte nicht synchronisiert werden.', true, true);
            });
        } else {
            setStatus('Offline: Verkäufe werden lokal gepuffert.', true);
        }

        if (syncButton) {
            syncButton.addEventListener('click', function () {
                syncQueuedSales().then(function (ok) {
                    return setStatus(ok ? 'Synchronisation abgeschlossen.' : 'Keine lokalen Verkäufe zum Synchronisieren.', false);
                }).catch(function () {
                    return setStatus('Synchronisation fehlgeschlagen.', true);
                });
            });
        }

        attachFormListener();
    });
})();
