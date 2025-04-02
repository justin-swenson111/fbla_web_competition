import requests
import json
from bs4 import BeautifulSoup

def fetch_scholarships(max_pages=5):  # Set a limit to prevent infinite loops
    base_url = "https://scholarships360.org/scholarships/scholarships-with-no-gpa-requirement/?sidebar_sort=relevant&current_page="
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    }

    scholarships = []

    for page in range(1, max_pages + 1):  
        url = f"{base_url}{page}&filter=all"
        response = requests.get(url, headers=headers)

        if response.status_code != 200:
            print(f"Failed to retrieve page {page}")
            break

        soup = BeautifulSoup(response.text, "html.parser")
        page_scholarships = []

        for item in soup.find_all("div", class_="re-scholarship-card-data-wrap"):  
            title_elem = (
                item.find("a", class_="re-verified_title") or 
                item.find("div", class_="scholarship-title") or 
                item.find("h2", class_="scholarship-name") or 
                item.find("span", class_="scholarship-title")
            )

            import re

            if title_elem:
                title = title_elem.get_text(strip=True)
                # Strip title until the first word ends (for example, after the first space)
                match = re.search(r'\s', title)  # Find the first space
                if match:
                    title = title[:match.start()]  # Get text before the first space
            else:
                # Fallback for items with no title_elem
                title = item.get_text(strip=True)[:100] + "..." if item else "No Title"
            
            # Get the scholarship link from the specific <a> element
            link_elem = item.find("a", class_="re-button re-button_green listPage_applyNow_clicks")
            link = link_elem.get('href', '#') if link_elem else '#'

            amount_elem = item.find("span", class_="re-scholarship-card-info-value")
            amount = amount_elem.text.strip() if amount_elem else "Amount Not Listed"
            
            details = item.find_all("span", class_="re-scholarship-card-info-value")
            deadline = details[1].text.strip() if len(details) > 1 else "No Deadline"
            grade_level = details[2].text.strip() if len(details) > 2 else "No Grade Level Info"

            verification_elem = item.find("span", class_="re-verified_title-tooltip")
            verification = "Verified" if verification_elem else "Not Verified"

            page_scholarships.append({
                "title": title,
                "link": link,
                "amount": amount,
                "deadline": deadline,
                "grade_level": grade_level,
                "verification_status": verification
            })

        if not page_scholarships:
            print(f"No scholarships found on page {page}, stopping.")
            break

        scholarships.extend(page_scholarships)
        print(f"Scraped page {page}, found {len(page_scholarships)} scholarships.")

    return scholarships

# Save to JSON
scholarship_data = fetch_scholarships(max_pages=5)  # Adjust `max_pages` as needed
with open("scholarships.json", "w", encoding="utf-8") as f:
    json.dump(scholarship_data, f, indent=4, ensure_ascii=False)

print(f"Saved {len(scholarship_data)} scholarships to scholarships.json")
