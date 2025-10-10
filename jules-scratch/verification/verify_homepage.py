from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    try:
        # Navigate to the running Laravel application
        page.goto("http://127.0.0.1:8000", timeout=60000)

        # Wait for the page to be fully loaded, just in case there are some JS operations
        page.wait_for_load_state('networkidle')

        # Take a screenshot of the homepage
        page.screenshot(path="jules-scratch/verification/homepage.png")

        print("Screenshot saved to jules-scratch/verification/homepage.png")

    except Exception as e:
        print(f"An error occurred: {e}")

    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)