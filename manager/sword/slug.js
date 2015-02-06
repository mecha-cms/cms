/**
 * Slug Generator
 * --------------
 */

(function($) {

    $.slug = function(input, output, connector) {

        input.off("keyup").on("keyup", function() {

            var value = this.value;

            // Remove accents ...
            var from = '¹²³°æǽÀÁÂÃÅǺĂǍÆǼàáâãåǻăǎª@ĈĊĉċ©ÐĐðđÈÉÊËĔĖèéêëĕėƒĜĠĝġĤĦĥħÌÍÎÏĨĬǏĮĲìíîïĩĭǐįĳĴĵĹĽĿĺľŀÑñŉÒÔÕŌŎǑŐƠØǾŒòôõōŏǒőơøǿºœŔŖŕŗŜȘŝșſŢȚŦÞţțŧþÙÚÛŨŬŰŲƯǓǕǗǙǛùúûũŭűųưǔǖǘǚǜŴŵÝŸŶýÿŷЪЬАБЦЧДЕЁЭФГХИЙЯЮКЛМНОПРСШЩТУВЫЗЖъьабцчдеёэфгхийяюклмнопрсшщтувызжÄÖÜßäöüÇĞİŞçğışĀĒĢĪĶĻŅŪāēģīķļņūҐІЇЄґіїєČĎĚŇŘŠŤŮŽčďěňřšťůžĄĆĘŁŃÓŚŹŻąćęłńóśźżΑΒΓΔΕΖΗΘΙΚΛΜΝΞΟΠΡΣΤΥΦΧΨΩΪΫάέήίΰαβγδεζηθικλμνξοπρςστυφχψωϊϋόύώϐϑϒأبتثجحخدذرزسشصضطظعغفقكلمنهويạảầấậẩẫằắặẳẵẹẻẽềếệểễịỉọỏồốộổỗờớợởỡụủừứựửữỳỵỷỹẠẢẦẤẬẨẪẰẮẶẲẴẸẺẼỀẾỆỂỄỊỈỌỎỒỐỘỔỖỜỚỢỞỠỤỦỪỨỰỬỮỲỴỶỸ',
                to = '1230aeaeAAAAAAAAAEAEaaaaaaaaaatCCcccDjDdjdEEEEEEeeeeeefGGggHHhhIIIIIIIIIJiiiiiiiiijJjLLLlllNnnOOOOOOOOOOOEooooooooooooeRRrrSSsssTTTTHtttthUUUUUUUUUUUUUuuuuuuuuuuuuuWwYYYyyyABCChDEEEFGHIJJaJuKLMNOPRSShShchTUVYZZhabcchdeeefghijjajuklmnoprsshshchtuvyzzhAEOEUEssaeoeueCGIScgisAEGIKLNUaegiklnuGIJiYegijiyeCDENRSTUZcdenrstuzACELNOSZZacelnoszzABGDEZEThIKLMNXOPRSTYPhChPsOIYaeeiYabgdezethiklmnxoprsstyphchpsoiyoyobthYabtthghkhdthrzsshsdtthaaghfkklmnhoyaaaaaaaaaaaaeeeeeeeeiioooooooooooouuuuuuuyyyyAAAAAAAAAAAAEEEEEEEEIIOOOOOOOOOOOOUUUUUUUYYYY';

            for (var i = 0, len = from.length; i < len; ++i) {
                value = value.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            output.val(
                value
                    .replace(/<.*?>/g, "")
                    .replace(/[^a-z0-9-]+/gi, '-')
                    .replace(/\-+/g, '-')
                    .replace(/^\-|\-$/g, "")
                    .toLowerCase()
                    .replace(/\-/g, connector)
            );

        });

        return input;

    };

    $.slugger = $.slug; // < 1.1.3

})(Zepto);