import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { news: Array }

    connect(){
        if(this.hasNewsValue && this.newsValue.length > 0){
            localStorage.setItem('fmp_news_cache', JSON.stringify(this.newsValue));
            console.log("Stimulus: News saved to LocalStorage", this.newsValue);
        }
    }

    async processStoredData(event){
        event.preventDefault();
        const cachedData = localStorage.getItem('fmp_news_cache');

        if(!cachedData){
            alert("No cached data found!");
            return;
        }

        const response = await fetch(event.currentTarget.getAttribute('href'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/html'
            },
            body: cachedData
        });

        if(response.ok){
            const html = await response.text();
            
            // Parse the incoming HTML string into a temporary document
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            
            // Look for the frame in the response
            const newFrame = doc.getElementById("news-frame");
            
            if (newFrame) {
                // Find the ACTUAL Turbo Frame in your browser and update its inner content (because the call has been shifted to a sub-div)
                // This ensures the frame stays but the content (including your controller div) refreshes
                const targetFrame = document.getElementById("news-frame");
                targetFrame.innerHTML = newFrame.innerHTML;
            }
        }
    }
}