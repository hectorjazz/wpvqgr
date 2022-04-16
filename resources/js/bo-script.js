/** -- SNIPPETS -- */

// Strip tags from HTML
function wpvqgr_strip_tags(html, limit)
{
	var tmp = document.createElement("DIV");
    tmp.innerHTML = html;
    var res = tmp.textContent || tmp.innerText || '';
    res.replace('\u200B', ''); // zero width space
    res = res.trim();

    if (limit > 0)
    {
        var trimmable = '\u0009\u000A\u000B\u000C\u000D\u0020\u00A0\u1680\u180E\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u2028\u2029\u3000\uFEFF';
        var reg = new RegExp('(?=[' + trimmable + '])');
        var words = res.split(reg);
        var count = 0;
        return words.filter(function(word) {
            count += word.length;
            return count <= limit;
        }).join('');
    }
    else
    {
    	return res;
    }
}

/** -- EVENTS -- */
(function($) 
{ 
	var wpvqgr_needToSave = false;
	var wpvqgr_safeClose = true;

	$(document).ready(function() 
	{
		$('#wpvqgr-aweber-generate-auth button').click(function(e)
		{
			var $div = $(this);
			e.preventDefault();

			$.post(wpvqgr.vars.bo_ajaxurl, {
	            'action': 'wpvqgr_bo_get_aweber_auth',
	            'wpvqgr_bo_nounce': wpvqgr.vars.bo_nounce,
	            'auth': $('#wpvqgr-aweber-generate-auth-field').val()
	        }, function(data) {
	        	$('input[name=_wpvqgr_settings_syncuser_aweber_apikey]').val(data);
	        });
		});

		// Prevent people from creating questions without saving personalities
		$('.container-carbon_fields_container_wpvq_quiz_perso_builder ul.carbon-tabs-nav li').click(function(e)
		{
			if (wpvqgr_needToSave) {
				alert(wpvqgr.vars.i18n_needSave);
				e.preventDefault();
				e.stopPropagation();
			}
		});

		/**
		 * Need to be updated with CarbonFields v3.0
		 */
		// $(document).on('carbonFields.apiLoaded', function(e, api) 
		// {
		// 	// When something changes
		// 	$(document).on('carbonFields.fieldUpdated', function(e, fieldName) 
		// 	{
		// 		// Need to save if user changes appreciations content
		// 		if (fieldName == 'wpvqgr_quiz_perso_appreciations' || fieldName.indexOf('/wpvqgr_quiz_appreciation_name') !== -1) {
		// 			wpvqgr_needToSave = true;
		// 		}
				
		// 	});
		// });

		/**
		 * Workaround waiting for CF v3.0
		 */
		
		$('#publish').click(function(){
			wpvqgr_safeClose = false;
		});
		
		window.addEventListener("beforeunload", function (e) {
			if (wpvqgr_safeClose) {
			    (e || window.event).returnValue = wpvqgr.vars.i18n_safeClose; //Gecko + IE
			    return wpvqgr.vars.i18n_safeClose; //Gecko + Webkit, Safari, Chrome etc.
			}
		});
	});
})(jQuery);