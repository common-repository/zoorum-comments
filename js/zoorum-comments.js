jQuery(document).ready(function() {
	Zoorum.init();
});


var Zoorum = {
	updateTimer: null,
	updateTimerTicker: -1,
	updateTimerTimes: [180000,5000,10000,30000,60000],
	maxUpdates: 10,
	currentUpdate: 0,

	init : function() {
		Zoorum.hideAndShowCommentForms();
		Zoorum.bindAllSubmits();
		Zoorum.startUpdates();
		Zoorum.initWidget();
	},
	initWidget : function() {
		if(jQuery('.zoorum-latest-feed').length > 0) {
			var zdata = {
	    		'action' : 'zoorum_get_feed'
		    };
			var zur = jQuery('.zoorum-ajax-url').html();
			jQuery.ajax({
	            type: "GET",
	            url: zur,
	            data: zdata,
	            dataType: 'json',
	            success: Zoorum.setWidgetContent,
	        });
		}
	},
	setWidgetContent : function(json) {
		if( !(json.status == 200) ) {
			if(json.status == 500 || json.status == 404) {
				//remove everything
			} else if(json.status == 302) {
				//correct subforum?
			} else {
				// Could not connect to server
			}
			return;
		}
		widgetStr = '';
		threads = [];
		if( json.data != null) {
			threads = json.data;
		}
		for (var i = 0; i < threads.length ; i++) {
			posts = [];
			if( threads[i].Posts != null) {
				posts = threads[i].Posts;
			}
			postsStr = '<div class="zoorum-widget-thread-header">';
			postsStr += '<a target="_blank" href="'+jQuery('.zoorum-widget-forum-url').attr('href')+threads[i].ThreadID+'">';
			postsStr += threads[i].Title+'</a></div>';
			hasPosts = false;
			for (var j = 0; j < posts.length ; j++) {
				if(posts[j].IsFirst) {
					/*postsStr += '<div class="zoorum-widget-post zoorum-widget-post-first">';*/
				} else { 
					postsStr += '<div class="zoorum-widget-post zoorum-widget-post-comment">';
					hasPosts = true;
					postsStr += '<a target="_blank" href="'+jQuery('.zoorum-widget-forum-url').attr('href')+threads[i].ThreadID+'">';
					postsStr += posts[j].User.Username;
					postsStr += ': ';
					postStr = posts[j].ParsedText.replace(/<(?:.|\n)*?>/gm, ' ');
					if ( postStr.length > 100 ) {
						postStr = postStr.substring(0, 97)+'...';
					}
					postsStr += postStr;
					postsStr += '</a>';
					postsStr += '</div>';
				}
			}
			if(hasPosts) {
				widgetStr += postsStr;
			}
		}
		jQuery('.zoorum-latest-feed').each(function(){
			widgetJQ = jQuery(widgetStr).hide();
			jQuery(this).append(widgetJQ);
			widgetJQ.slideDown();
		});
	},
	
	
	hideAndShowCommentForms : function() {
		if (!jQuery('.zoorum-thread').hasClass('.islocked')) {
			/* hide with js, reassure non-js behaviour*/
			jQuery('.zoorum-comment-form-wrapper.zoorum-comment').each( function() {
				jQuery(this).hide();
			});
			/* open comment form when comment-link is clicked */
			jQuery('.zoorum-comment-link').each( function() {
				Zoorum.bindCommentClick(this);
			});
		}
		
	},
	bindCommentClick : function(el) {
		jQuery(el).click( function() {
			shownEl = jQuery('#zoorum-comment-form-wrapper-'+jQuery(el).children('.id-to-comment').html());
			jQuery('.zoorum-comment-form-wrapper.zoorum-comment:not('+'#zoorum-comment-form-wrapper-'+jQuery(el).children('.id-to-comment').html()+')').slideUp();
			shownEl.slideDown();
			jQuery(shownEl).find('#zoorum-message').focus();
			return false;
		});
	},

	bindCommentFormChange : function(el) {
		jQuery(el).find('#zoorum-message').on('keyup', function() {
			jQuery(this).css('height', 'auto');
			jQuery(this).scrollTop(0);
			offset = jQuery(this)[0].offsetHeight - jQuery(this)[0].clientHeight;
			jQuery(this).css('height', jQuery(this).prop('scrollHeight')+offset);
		});
	},



	/* Submit handlers and bindings */
	bindAllSubmits : function() {
		jQuery('.zoorum-submit-create').each( function() {
			Zoorum.bindCreateSubmit(this);
			Zoorum.bindCommentFormChange(this);
		});
		jQuery('.zoorum-submit-comment').each(function(){ 
			Zoorum.bindSingleSubmit(this);
			Zoorum.bindCommentFormChange(this);
		});
	},
	bindCreateSubmit : function(el) {
		jQuery(el).submit( function() {
			var zdata = {
	    		'action' : 'zoorum_create_topic',
	    		'zoorum_wp_pid' : jQuery('#zoorum-wp-pid').html(),
	    	};
	        var zur = jQuery('#zoorum-ajax-url').html();
	        var response = jQuery.parseJSON(jQuery.ajax({
	            type: "POST",
	            url: zur,
	            data: zdata,
	            async: false,
	            dataType: 'json'
	        }).responseText );
			if( !(response.status == 200) ) {
				jQuery('#zoorum-comments-error').html(response.data); //felutskrift
				return false;
			} else {
				jQuery('.zoorum-submit-create').children('#zoorum-id').val(response.data);
				jQuery(this).children('#post-message').val( jQuery(this).children('#zoorum-message').val() );
				jQuery(this).children('#zoorum-message').val('');
				
				jQuery(this).unbind();
				Zoorum.updateTimerTicker = 1;
				Zoorum.runUpdates();
				return true;
			}
	        return false;
		});
	},
	bindSingleSubmit : function(el) {
		jQuery(el).submit( function() {
			jQuery(this).children('#post-message').val( jQuery(this).children('#zoorum-message').val() );
			jQuery(this).children('#zoorum-message').val('');
			jQuery(this).closest('.zoorum-comment-form-wrapper.zoorum-comment').slideUp();
			Zoorum.updateTimerTicker = 1;
			Zoorum.runUpdates();
			return true;
		});
	},
	startUpdates : function() {
		Zoorum.updateTimerTicker = 0; //comment this line to start with delay
		Zoorum.runUpdates();
	},
	runUpdates : function() {
		clearTimeout(Zoorum.updateTimer);
		switch (Zoorum.updateTimerTicker) {
			case -1:
				Zoorum.updateTimerTicker = 0;
				Zoorum.updateTimer = setTimeout(Zoorum.runUpdates, Zoorum.updateTimerTimes[Zoorum.updateTimerTicker]);
				break;
			case 0:
				if ( !(Zoorum.currentUpdate > Zoorum.maxUpdates) ) {
					Zoorum.updateTimer = setTimeout(Zoorum.runUpdates, Zoorum.updateTimerTimes[Zoorum.updateTimerTicker]);
					Zoorum.fetchUpdates();
					Zoorum.currentUpdate++;
				}
				break;
			case 1:
				Zoorum.updateTimer = setTimeout(Zoorum.runUpdates, Zoorum.updateTimerTimes[Zoorum.updateTimerTicker]);
				Zoorum.updateTimerTicker++;
				break;
			case 2:
			case 3:
				Zoorum.updateTimer = setTimeout(Zoorum.runUpdates, Zoorum.updateTimerTimes[Zoorum.updateTimerTicker]);
				Zoorum.fetchUpdates();
				Zoorum.updateTimerTicker++;
				break;
			case 4:
				Zoorum.updateTimer = setTimeout(Zoorum.runUpdates, Zoorum.updateTimerTimes[Zoorum.updateTimerTicker]);
				Zoorum.fetchUpdates();
				Zoorum.updateTimerTicker = 0;
				Zoorum.currentUpdate = 0;
				break;
		}
		
	},
	fetchUpdates : function() {
		var zdata = {
	    		'action' : 'zoorum_get_topic',
	    		'zoorum_wp_pid' : jQuery('#zoorum-wp-pid').html(),
	    };
		var zur = jQuery('#zoorum-ajax-url').html();
		jQuery.ajax({
            type: "POST",
            url: zur,
            data: zdata,
            dataType: 'json',
            success: Zoorum.parseJson,
        });
	},
	parseJson : function(json) {
		if( !(json.status == 200) ) {
			jQuery('#zoorum-comments-error').html(json.data);
			if(json.status == 500 || json.status == 404) {
				//remove everything
				Zoorum.currentUpdate = 0;
				Zoorum.cleanOnDeletedPost();
			} else if(json.status == 302) {
				//correct subforum?
			} else {
				// Could not connect to server
			}
			return;
		}
		jQuery('#zoorum-comments-error').html('');

		postElements = jQuery('.zoorum-post');
		if(postElements.length == 0 && jQuery('.zoorum-submit-create').length > 0) {
			jQuery('.zoorum-link').attr('href', jQuery('#zoorum-url').html()+json.data.ThreadID);
			Zoorum.createToReplyForm(json.data.ThreadID);
		}


		/* explanation to following
		 * - loop through all post elements -
		 *     if found on expected index(lastid) in json reply -> update 
		 *     if found later than expected -> add all posts between lastid and currentid in json reply
		 *     if not found in json reply -> tag for removal
		 * - add all new posts trailing last found index(lastid) -
		 * - remove all tagged elements -
		 * - variables -
		 *     lastid: the index for the last element found in json
		 *     idToComment: the latest "reply" in json
		 *     elementBefore: the element the next comment should be inserted after
		 * */
		posts = [];
		if( json.data != null && json.data.Posts != null) {
			
			posts = json.data.Posts;
		}
		lastid = -1;
		idToComment = 0;
		elementBefore = jQuery('#zoorum-comment-create-values').first();
		postElements.each(function() { //EACH ELEMENT
			mid = jQuery(this).attr('id').substring(4);
			found = false;
			for (var i = lastid+1; i < posts.length; i++) { //EACH POST
				if (mid == posts[i].PostID) { //Correct item
					if (lastid == i-1) { //Item at expected place
						if (!posts[i].IsComment) { idToComment = posts[i].PostID; }
						lastid = i;
					} else if (lastid < i-1) { //Add everything from lastid+1 to i-1
						for (var j = lastid+1; j < i; j++) {
							if (!posts[j].IsComment) { idToComment = posts[j].PostID; }
							elementBefore = Zoorum.insertNewPost(Zoorum.createNewPost(posts[j], idToComment), elementBefore);
							Zoorum.currentUpdate = 0;
						}
						lastid = i;
					}
					found = true;
					break;
				}
			} //EACH POST
			if (!found) {
				jQuery(this).addClass('zoorum-remove');
			} else {
				elementBefore = jQuery(this);
			}
		}); //EACH ELEMENT
		for (var i = lastid+1; i < posts.length; i++) {
			if (!posts[i].IsComment) { idToComment = posts[i].PostID; }
			if ( jQuery('#zoorum-comment-form-wrapper-'+idToComment).length == 0 ) {
				if(jQuery('.zoorum-comment-form-wrapper.zoorum-comment').length > 0) {
					elementBefore = jQuery('.zoorum-comment-form-wrapper.zoorum-comment').last();
				}
				Zoorum.insertCommentForm(Zoorum.createCommentForm(idToComment), elementBefore);
			}
			elementBefore = Zoorum.insertNewPost(Zoorum.createNewPost(posts[i], idToComment), elementBefore);
			Zoorum.currentUpdate = 0;
		}
		jQuery('.zoorum-remove').each( function(){Zoorum.deleteOldPost(this);} );
	},

	createToReplyForm : function(threadID) {
			replyForm = jQuery('.zoorum-submit-create');
			replyForm.removeClass('zoorum-submit-create');
			replyForm.addClass('zoorum-submit-comment');
			replyForm.find('#zoorum-id').val(threadID);
			replyForm.unbind();
			Zoorum.bindSingleSubmit(replyForm);
	},

	cleanOnDeletedPost : function() {
		if( jQuery('.zoorum-post').length > 0) {
			jQuery('.zoorum-comment-form-wrapper').remove();
			jQuery('.zoorum-post').remove();
			jQuery('.zoorum-comment').remove();
			jQuery('.zoorum-footer').before(jQuery('#zoorum-form-create-template').html());
			jQuery('.zoorum-link').attr('href', jQuery('#zoorum-url').html());
			Zoorum.bindAllSubmits();
		}
	},

	/* create delete and insert new posts and forms */
	createNewPost : function(post, idToComment) {
		el = jQuery(jQuery('#zoorum-comment-template').html());
		el.attr('id', 'post'+post.PostID);
		if (post.IsComment) {
			el.addClass('zoorum-comment');
		}
		if (post.IsFirst) {
			el.addClass('zoorum-first');
		}
		jQuery(el).find('.zoorum-posttext').html(post.User.Username+': '+post.ParsedText);
		jQuery(el).find('.zoorum-datestamp').attr('title', post.ReadableDate);
		jQuery(el).find('.zoorum-datestamp').html(post.TimeDiffDate);
		jQuery(el).find('.id-to-comment').html(idToComment);
		jQuery(el).find('.zoorum-comment-link').each( function() {
			Zoorum.bindCommentClick(this);
		});
		return el;
	},
	insertNewPost : function(newElement, elementBefore) {
		newElement.hide();
		elementBefore.after(newElement);
		if ( !(newElement.hasClass('zoorum-first')) ) {
			newElement.slideDown();
		}
		return newElement;
	},
	deleteOldPost : function(el) {
		jQuery(el).slideUp(400, function(){ this.remove(); });
	},
	createCommentForm : function(idToComment) {
		el = jQuery(jQuery('#zoorum-form-comment-template').html());
		el.attr('id', 'zoorum-comment-form-wrapper-'+idToComment);
		jQuery(el).find('#zoorum-id').val(idToComment);
		jQuery(el).find('.zoorum-submit-comment').each(function() {
			Zoorum.bindSingleSubmit(this);
		});
		Zoorum.bindCommentFormChange(el);
		el.hide();
		return el;
	},
	insertCommentForm : function(newElement, elementBefore) {
		elementBefore.after(newElement);
	}
}

