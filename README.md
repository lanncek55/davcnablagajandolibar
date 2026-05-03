# davcnablagajandolibar

Implementacija slovenske davčne blagajne (FURS) za ERP sistem Dolibarr (v23.0.2).

Modul omogoča davčno potrjevanje računov pri Finančni upravi Republike Slovenije v skladu z zakonodajo o davčnem potrjevanju računov. Podpira tako testno (VDZ) kot produkcijsko okolje.

## Funkcionalnosti

- Potrjevanje gotovinskih in/ali negotovinskih računov.
- Podpora za avansne račune (predračune z gotovinskim plačilom) in stornacije/dobropise s sklicem na originalni račun.
- Avtomatsko pošiljanje računov na FURS in pridobivanje EOR (Enotna Oznaka Računa).
- Avtomatsko generiranje in podpisovanje ZOI (Zaščitna Oznaka Izdajatelja).
- Varno nalaganje in uporaba namenskega `.p12` digitalnega potrdila (FURS certifikat).
- Shranjevanje logov in XML zahtevkov v bazo (`llx_furs_log`).

## Namestitev

1. **Kopiranje datotek:**
   Prenesite vsebino repozitorija (mapo `furs`) v vašo Dolibarr namestitev. Mapo lahko namestite bodisi v glavno mapo modula:
   `htdocs/custom/furs` (priporočljivo, če imate omogočeno `custom` mapo v `conf.php`)
   ali neposredno v `htdocs/furs`.

2. **Aktivacija modula:**
   Prijavite se v Dolibarr kot administrator.
   Pojdite na **Nastavitve (Setup)** > **Moduli/Aplikacije (Modules/Applications)**.
   Poiščite modul z imenom **Davčno potrjevanje računov (FURS Slovenija)** in ga omogočite s klikom na stikalo. Ob aktivaciji se bodo avtomatsko ustvarile potrebne tabele v bazi.

3. **Konfiguracija modula:**
   Po aktivaciji kliknite na ikono za nastavitve (zobnik) ob modulu FURS.
   - **Okolje:** Izberite "Test (VDZ)" za preizkušanje ali "Produkcija" za pravo poslovanje.
   - **Način potrjevanja:** Izberite, ali želite na FURS pošiljati vse račune ali "Samo gotovinske račune". *Pozor: V primeru izbire samo gotovinskih računov zagotovite, da imate v Dolibarru nastavljeno različno masko za številčenje gotovinskih in negotovinskih računov.*
   - **Nalaganje certifikata:** Naložite vaš `.p12` certifikat, ki ste ga pridobili pri FURS-u in vnesite pripadajoče geslo.

4. **Davčna številka:**
   Poskrbite, da imate v Dolibarru pod **Nastavitve** > **Podjetje/Organizacija** pravilno vpisano vašo davčno številko (v polju `Tax/VAT number` oz. SI+davčna).

## Uporaba

Ko je modul pravilno konfiguriran, deluje avtomatsko v ozadju. Ob vsaki potrditvi (validaciji) računa se bo ta (če ustreza pogojem iz nastavitev modula in glede na način plačila) kriptografsko podpisal in prek SOAP protokola poslal na FURS. Če bo potrditev uspešna, bo pridobljen EOR, račun pa se bo normalno potrdil. V primeru napak (npr. neveljaven certifikat ali napačni podatki) bo potrditev računa zavrnjena, uporabniku pa se bo izpisala napaka, ki jo je vrnil FURS.