import requests
from bs4 import BeautifulSoup
import json

def fetch_scholarships():
    url = "https://scholarships360.org/scholarships/scholarships-with-no-gpa-requirement/?sidebar_sort=relevant&current_page=1&filter=all"
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    }
    
    response = requests.get(url, headers=headers)
    
    if response.status_code != 200:
        print("Failed to retrieve the page")
        return []

    soup = BeautifulSoup(response.text, "html.parser")
    
    scholarships = []
    
    for item in soup.find_all("div", class_="re-scholarship-card-data-wrap"):  
        # Title and link - multiple methods to find the title
        title_elem = (
            item.find("a", class_="re-verified_title") or 
            item.find("div", class_="scholarship-title") or 
            item.find("h2", class_="scholarship-name") or 
            item.find("span", class_="scholarship-title")
        )
        
        # Fallback title extraction
        if title_elem:
            title = title_elem.get_text(strip=True)
            link = title_elem.get('href', '#')
        else:
            # If no title found, try to extract from the entire card
            title = item.get_text(strip=True)[:100] + "..." if item else "No Title"
            link = "#"
        
        # Amount
        amount_elem = item.find("span", class_="re-scholarship-card-info-value")
        amount = amount_elem.text.strip() if amount_elem else "Amount Not Listed"
        
        # Deadline
        details = item.find_all("span", class_="re-scholarship-card-info-value")
        deadline = details[1].text.strip() if len(details) > 1 else "No Deadline"
        
        # Grade Level
        grade_level = details[2].text.strip() if len(details) > 2 else "No Grade Level Info"
        
        # Verification Status
        verification_elem = item.find("span", class_="re-verified_title-tooltip")
        verification = "Verified" if verification_elem else "Not Verified"

        scholarships.append({
            "title": title,
            "link": link,
            "amount": amount,
            "deadline": deadline,
            "grade_level": grade_level,
            "verification_status": verification
        })

    return scholarships

# Save scholarships to JSON
scholarship_data = fetch_scholarships()
with open("scholarships.json", "w", encoding="utf-8") as f:
    json.dump(scholarship_data, f, indent=4, ensure_ascii=False)

print(f"Saved {len(scholarship_data)} scholarships to scholarships.json")