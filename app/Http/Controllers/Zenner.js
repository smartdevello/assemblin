// 2023 https://codeberg.org/loracodede/lorawan_payload_decoder
// Dieser Code unterliegt der GNU General Public License und wird ohne Gewährleistung bereitgestellt.
/**
* Dekodiert einen Uplink von einem Rauchwarnmelder.
*
* @param {Object} input Uplink-Objekt mit folgenden Eigenschaften:
* @param {Uint8Array} input.bytes Rohdaten des Uplinks als Array von Bytes
* @param {number} input.fPort Port, über den der Uplink gesendet wurde
* @param {Date} [input.transceived_at] Zeitstempel des Uplink-Empfangs
*
* @returns {Object} Objekt mit den Dekodierergebnissen:
* @property {Object} data Dekodierte Nutzdaten
* @property {Object} warnings Warnungen bei der Dekodierung
* @property {Object} errors Fehler bei der Dekodierung
*/
function decodeUplink(input) {
    // Objekte für die Decodierten Daten, Warnungen und Fehler anlegen
    var d = {}, warnings = {}, errors = {};
    d.value1 = [], d.value2 = [];
    // Übernehmen des Empfangs-Timestamps aus den Metadaten
    d.transceived_at = input.transceived_at ?? new Date().toISOString();
    // Rohdatenbytes in hexadezimal umwandeln
    const b = Array.from(input.bytes, b => b.toString(16).padStart(2, '0'))
    .join('').match(/.{1,2}(?=(.{2})+(?!.))|.{1,2}$/g)
    .map(hex => parseInt(hex, 16).toString(16).padStart(2, '0').toUpperCase());
    // Funktion zum Parsen eines hexadezimalen Datums- und Zeitwerts
    const datetime = h => {
        const hex = parseInt(h, 16);
        const [dt0, dt1, dt2, dt3] = [hex & 0xff, (hex >> 8) & 0xff, (hex >> 16) & 0xff, (hex >> 24) & 0xff];
        const year = ((dt2 & 0xE0) >> 5) | ((dt3 & 0xF0) >> 1) + 2000;
        const month = (dt3 & 0x0F) - 1;
        const day = dt2 & 0x1F;
        const hour = dt1 & 0x1F;
        const minute = dt0 & 0x3F;
        const date = new Date(year, month, day, hour, minute);
        const currentYear = new Date().getFullYear();
        return date.getFullYear() <= currentYear ? date.toISOString() : false;
    };
    // Zuordnung hexadezimaler Alarmcodes zu Klartext
    const alarms = {
        "0200": 'BatteryEndOfLife',
        "0800": 'SmokeChamberPollutionPrewarning',
        "1000": 'SmokeChamberPollutionWarning',
        "2000": 'TestButtonFailure',
        "4000": 'AcousticAlarmFailure',
        "8000": 'RemovalDetection',
        "0001": 'TestAlarm',
        "0002": 'SmokeAlarm',
        "0004": 'ObstructionDetection',
        "0008": 'SurroundingAreaMonitoring',
        "0010": 'LEDFailure'
    };
    function getAlarmFromHex(hexValue) {
        const lsbHexValue = hexValue.slice(-2) + hexValue.slice(0, -2);
        const decimalValue = Number(`0x${lsbHexValue}`);
        const alarm = Object.entries(alarms)
        .filter(([key]) => decimalValue & parseInt(key, 16))
        .map(([, value]) => value)
        .join(", ");
        return alarm ? alarm : "OKAY";
    }
    // Dekodierung eines hexadezimalen Statuswerts in Klartext
    function decodeRadioStatus(payload) {
        const decimalPayload = parseInt(payload, 16);
        const statusBits = {
        0: "Removal detected",
        2: "Battery end of life",
        3: "Acoustic alarm failure",
        4: "Obstruction detection",
        5: "Surrounding area monitoring"
        };
        const status = Object.entries(statusBits)
        .filter(([bit, _]) => decimalPayload & (1 << bit))
        .map(([_, message]) => message)
        .join(", ");
        return status;
    }
    function APCode(payload) {
        const errorCodes = {
        "02": "Removal",
        "0C": "Battery end of life",
        "16": "Horn drive level failure",
        "1A": "Obstruction detection",
        "19": "Smoke alarm released (only in some CommunicationScenarios)",
        "1C": "Object in the surrounding area"
        };
        return Object.entries(errorCodes)
        .filter(([code]) => payload.includes(code))
        .map(([code, description]) => description)
        .join(", ");
    };
    // Dekodierung für Pakettyp SP1.1
    if (b[0] == "11") {
        d.packetType = "SP1.1";
        d.packetName = "day value";
        d.message_art = "Status report";
        if (getAlarmFromHex([b[1], b[2]].join("")) == "OKAY") {
        d.status = 1;
        } else {
        d.status = 2;
        d.warning_current = getAlarmFromHex([b[1], b[2]].join(""))
        warnings.message = getAlarmFromHex([b[1], b[2]].join(""))
        }
    }
    // Dekodierung für Pakettyp SP9.1
    if (b[0] == "91") {
        d.packetType = "SP9.1";
        d.packetName = "current date and time & status summary";
        d.message_art = "Status report";
        d.timestamp = datetime([b[1], b[2], b[3], b[4]].reverse().join(""));
        // Statuswert dekodieren
        const statussummary = b[5] + " " + b[6];
        if (statussummary == "00 00") {
        d.status = 1;
        } else {
        d.status = 2;
        d.warning_current = decodeRadioStatus(statussummary);
        warnings.message = decodeRadioStatus(statussummary);
        }
    }
    // Dekodierung für Pakettyp SP9.2
    if (b[0] == "92") {
        d.packetType = "SP9.2";
        d.packetName = "static device information";
        d.message_art = "Status report";
        // Versionsnummern parsen
        d.firmwareVersion = [b[1], b[2], b[3], b[4]].reverse().join("").match(/.{1,2}/g).map(n => parseInt(n)).join('.');
        d.loraWanVersion = [b[5], b[6], b[7]].reverse().join("").match(/.{1,2}/g).map(n => parseInt(n)).join('.');
        d.loraCommandVersion = [b[8], b[9]].reverse().join("").match(/.{1,2}/g).map(n => parseInt(n)).join('.');
        d.minolDeviceType = parseInt(b[10].match(/.{1,2}/g).map(n => parseInt(n)).join('.'));
        // Geräte-ID parsen
        d.meterId = parseInt([b[11], b[12], b[13], b[14]].reverse().join(""), 16);
    }
    // Dekodierung für Pakettyp AP1.0
    if (b[0] == "A0") {
        d.packetType = "AP1.0";
        d.packetName = "status code, status data";
        d.message_art = "malfunction report"
        d.timestamp = datetime(["00", "00", b[3], b[4]].reverse().join(""));
        d.status = 2;
        d.warning_current = APCode(b[1]);
        warnings.message = APCode(b[1]);
        errors.message = APCode(b[1]);
    }
    // Rückgabe des dekodierten JSON-Objekts
    return { data: d, warnings, errors };
}