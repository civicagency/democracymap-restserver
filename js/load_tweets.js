feeds = $('ul.jurisdiction-list').find('div.twitter-feed')

for(var i = 0, len = feeds.length; i < len; ++i) {
    
$(feeds[i]).tweet({
username: feeds[i].id,
avatar_size: 32,
count: 4,
loading_text: "loading tweets..."
});

      
}