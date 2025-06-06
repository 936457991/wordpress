//script list ( ISO 15924 )
const script = [
    ["Adlam", "Adlm"],
    ["Afaka", "Afak"],
    ["Caucasian Albanian", "Aghb"],
    ["Ahom, Tai Ahom", "Ahom"],
    ["Arabic", "Arab"],
    ["Arabic (Nastaliq variant)", "Aran"],
    ["Imperial Aramaic", "Armi"],
    ["Armenian", "Armn"],
    ["Avestan", "Avst"],
    ["Balinese", "Bali"],
    ["Bamum", "Bamu"],
    ["Bassa Vah", "Bass"],
    ["Batak", "Batk"],
    ["Bengali (Bangla)", "Beng"],
    ["Bhaiksuki", "Bhks"],
    ["Blissymbols", "Blis"],
    ["Bopomofo", "Bopo"],
    ["Brahmi", "Brah"],
    ["Braille", "Brai"],
    ["Buginese", "Bugi"],
    ["Buhid", "Buhd"],
    ["Chakma", "Cakm"],
    ["Unified Canadian Aboriginal Syllabics", "Cans"],
    ["Carian", "Cari"],
    ["Cham", "Cham"],
    ["Cherokee", "Cher"],
    ["Chorasmian", "Chrs"],
    ["Cirth", "Cirt"],
    ["Coptic", "Copt"],
    ["Cypro-Minoan", "Cpmn"],
    ["Cypriot syllabary", "Cprt"],
    ["Cyrillic", "Cyrl"],
    ["Cyrillic (Old Church Slavonic variant)", "Cyrs"],
    ["Devanagari (Nagari)", "Deva"],
    ["Dives Akuru", "Diak"],
    ["Dogra", "Dogr"],
    ["Deseret (Mormon)", "Dsrt"],
    ["Duployan shorthand, Duployan stenography", "Dupl"],
    ["Egyptian demotic", "Egyd"],
    ["Egyptian hieratic", "Egyh"],
    ["Egyptian hieroglyphs", "Egyp"],
    ["Elbasan", "Elba"],
    ["Elymaic", "Elym"],
    ["Ethiopic (Geʻez)", "Ethi"],
    ["Khutsuri (Asomtavruli and Nuskhuri)", "Geok"],
    ["Georgian (Mkhedruli and Mtavruli)", "Geor"],
    ["Glagolitic", "Glag"],
    ["Gunjala Gondi", "Gong"],
    ["Masaram Gondi", "Gonm"],
    ["Gothic", "Goth"],
    ["Grantha", "Gran"],
    ["Greek", "Grek"],
    ["Gujarati", "Gujr"],
    ["Gurmukhi", "Guru"],
    ["Han with Bopomofo (alias for Han + Bopomofo)", "Hanb"],
    ["Hangul (Hangŭl, Hangeul)", "Hang"],
    ["Han (Hanzi, Kanji, Hanja)", "Hani"],
    ["Hanunoo (Hanunóo)", "Hano"],
    ["Han (Simplified variant)", "Hans"],
    ["Han (Traditional variant)", "Hant"],
    ["Hatran", "Hatr"],
    ["Hebrew", "Hebr"],
    ["Hiragana", "Hira"],
    ["Anatolian Hieroglyphs (Luwian Hieroglyphs, Hittite Hieroglyphs)", "Hluw"],
    ["Pahawh Hmong", "Hmng"],
    ["Nyiakeng Puachue Hmong", "Hmnp"],
    ["Japanese syllabaries (alias for Hiragana + Katakana)", "Hrkt"],
    ["Old Hungarian (Hungarian Runic)", "Hung"],
    ["Indus (Harappan)", "Inds"],
    ["Old Italic (Etruscan, Oscan, etc.)", "Ital"],
    ["Jamo (alias for Jamo subset of Hangul)", "Jamo"],
    ["Javanese", "Java"],
    ["Japanese (alias for Han + Hiragana + Katakana)", "Jpan"],
    ["Jurchen", "Jurc"],
    ["Kayah Li", "Kali"],
    ["Katakana", "Kana"],
    ["Kharoshthi", "Khar"],
    ["Khmer", "Khmr"],
    ["Khojki", "Khoj"],
    ["Khitan large script", "Kitl"],
    ["Khitan small script", "Kits"],
    ["Kannada", "Knda"],
    ["Korean (alias for Hangul + Han)", "Kore"],
    ["Kpelle", "Kpel"],
    ["Kaithi", "Kthi"],
    ["Tai Tham (Lanna)", "Lana"],
    ["Lao", "Laoo"],
    ["Latin (Fraktur variant)", "Latf"],
    ["Latin (Gaelic variant)", "Latg"],
    ["Latin", "Latn"],
    ["Leke", "Leke"],
    ["Lepcha (Róng)", "Lepc"],
    ["Limbu", "Limb"],
    ["Linear A", "Lina"],
    ["Linear B", "Linb"],
    ["Lisu (Fraser)", "Lisu"],
    ["Loma", "Loma"],
    ["Lycian", "Lyci"],
    ["Lydian", "Lydi"],
    ["Mahajani", "Mahj"],
    ["Makasar", "Maka"],
    ["Mandaic, Mandaean", "Mand"],
    ["Manichaean", "Mani"],
    ["Marchen", "Marc"],
    ["Mayan hieroglyphs", "Maya"],
    ["Medefaidrin (Oberi Okaime, Oberi Ɔkaimɛ)", "Medf"],
    ["Mende Kikakui", "Mend"],
    ["Meroitic Cursive", "Merc"],
    ["Meroitic Hieroglyphs", "Mero"],
    ["Malayalam", "Mlym"],
    ["Modi, Moḍī", "Modi"],
    ["Mongolian", "Mong"],
    ["Moon (Moon code, Moon script, Moon type)", "Moon"],
    ["Mro, Mru", "Mroo"],
    ["Meitei Mayek (Meithei, Meetei)", "Mtei"],
    ["Multani", "Mult"],
    ["Myanmar (Burmese)", "Mymr"],
    ["Nandinagari", "Nand"],
    ["Old North Arabian (Ancient North Arabian)", "Narb"],
    ["Nabataean", "Nbat"],
    ["Newa, Newar, Newari, Nepāla lipi", "Newa"],
    ["Naxi Dongba (na²¹ɕi³³ to³³ba²¹, Nakhi Tomba)", "Nkdb"],
    ["Naxi Geba (na²¹ɕi³³ gʌ²¹ba²¹, 'Na-'Khi ²Ggŏ-¹baw, Nakhi Geba)", "Nkgb"],
    ["N’Ko", "Nkoo"],
    ["Nüshu", "Nshu"],
    ["Ogham", "Ogam"],
    ["Ol Chiki (Ol Cemet’, Ol, Santali)", "Olck"],
    ["Old Turkic, Orkhon Runic", "Orkh"],
    ["Oriya (Odia)", "Orya"],
    ["Osage", "Osge"],
    ["Osmanya", "Osma"],
    ["Old Uyghur", "Ougr"],
    ["Palmyrene", "Palm"],
    ["Pau Cin Hau", "Pauc"],
    ["Proto-Cuneiform", "Pcun"],
    ["Proto-Elamite", "Pelm"],
    ["Old Permic", "Perm"],
    ["Phags-pa", "Phag"],
    ["Inscriptional Pahlavi", "Phli"],
    ["Psalter Pahlavi", "Phlp"],
    ["Book Pahlavi", "Phlv"],
    ["Phoenician", "Phnx"],
    ["Miao (Pollard)", "Plrd"],
    ["Klingon (KLI pIqaD)", "Piqd"],
    ["Inscriptional Parthian", "Prti"],
    ["Proto-Sinaitic", "Psin"],
    ["Reserved for private use (start)", "Qaaa"],
    ["Reserved for private use (end)", "Qabx"],
    ["Ranjana", "Ranj"],
    ["Rejang (Redjang, Kaganga)", "Rjng"],
    ["Hanifi Rohingya", "Rohg"],
    ["Rongorongo", "Roro"],
    ["Runic", "Runr"],
    ["Samaritan", "Samr"],
    ["Sarati", "Sara"],
    ["Old South Arabian", "Sarb"],
    ["Saurashtra", "Saur"],
    ["SignWriting", "Sgnw"],
    ["Shavian (Shaw)", "Shaw"],
    ["Sharada, Śāradā", "Shrd"],
    ["Shuishu", "Shui"],
    ["Siddham, Siddhaṃ, Siddhamātṛkā", "Sidd"],
    ["Khudawadi, Sindhi", "Sind"],
    ["Sinhala", "Sinh"],
    ["Sogdian", "Sogd"],
    ["Old Sogdian", "Sogo"],
    ["Sora Sompeng", "Sora"],
    ["Soyombo", "Soyo"],
    ["Sundanese", "Sund"],
    ["Syloti Nagri", "Sylo"],
    ["Syriac", "Syrc"],
    ["Syriac (Estrangelo variant)", "Syre"],
    ["Syriac (Western variant)", "Syrj"],
    ["Syriac (Eastern variant)", "Syrn"],
    ["Tagbanwa", "Tagb"],
    ["Takri, Ṭākrī, Ṭāṅkrī", "Takr"],
    ["Tai Le", "Tale"],
    ["New Tai Lue", "Talu"],
    ["Tamil", "Taml"],
    ["Tangut", "Tang"],
    ["Tai Viet", "Tavt"],
    ["Telugu", "Telu"],
    ["Tengwar", "Teng"],
    ["Tifinagh (Berber)", "Tfng"],
    ["Tagalog (Baybayin, Alibata)", "Tglg"],
    ["Thaana", "Thaa"],
    ["Thai", "Thai"],
    ["Tibetan", "Tibt"],
    ["Tirhuta", "Tirh"],
    ["Tangsa", "Tnsa"],
    ["Toto", "Toto"],
    ["Ugaritic", "Ugar"],
    ["Vai", "Vaii"],
    ["Visible Speech", "Visp"],
    ["Vithkuqi", "Vith"],
    ["Warang Citi (Varang Kshiti)", "Wara"],
    ["Wancho", "Wcho"],
    ["Woleai", "Wole"],
    ["Old Persian", "Xpeo"],
    ["Cuneiform, Sumero-Akkadian", "Xsux"],
    ["Yezidi", "Yezi"],
    ["Yi", "Yiii"],
    ["Zanabazar Square (Zanabazarin Dörböljin Useg, Xewtee Dörböljin Bicig, Horizontal Square Script)", "Zanb"],
    ["Code for inherited script", "Zinh"],
    ["Mathematical notation", "Zmth"],
    ["Symbols (Emoji variant)", "Zsye"],
    ["Symbols", "Zsym"],
    ["Code for unwritten documents", "Zxxx"],
    ["Code for undetermined script", "Zyyy"],
    ["Code for uncoded script", "Zzzz"]
];

export default script;