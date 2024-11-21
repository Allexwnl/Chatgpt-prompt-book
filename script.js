const promptTemplate = document.getElementById('prompt-option');
const promptOptionsDisplay = document.getElementById('prompt-options-display');
const promptTitleElement = document.getElementById('prompt-title');
const promptTextarea = document.getElementById('prompt');
const use_case = document.getElementById('use_case');
const category = document.getElementById('tags');
const askButton = document.getElementById('askChatGPT');
const saveButton = document.getElementById('saveprompt');
const loginButton = document.getElementById('login');
const registerButton = document.getElementById('register');

function LogInCheck() {
    $.ajax({
        method: "POST",
        url: "Data.php",
        data: { 
            function: "LogInCheck",
        }
      })
        .done(function( response ) {
            if(response == "Log in first")
            {
                window.location.href = "login.html";
            }
            console.log(response);
        });
}

async function getPrompts() {
    LogInCheck();
    return await $.ajax({
        method: "POST",
        url: "Data.php",
        data: { 
            function: "getPrompts",
        }
      })
        .done(function( response ) {
            return response;
        });
}

function pushPrompt(prompt, category, usecase) {
    LogInCheck();
    $.ajax({
        method: "POST",
        url: "Data.php",
        data: { 
            function: "pushPrompt",
            prompt: prompt,
            category: category,
            usecase: usecase,
        }
      })
        .done(function( response ) {
            console.log(response);
        });
}

function checkIfUser(username, password) {
    $.ajax({
        method: "POST",
        url: "Data.php",
        data: { 
            function: "checkIfUser",
            username: username,
            password: password,
        }
      })
        .done(function( response ) {
            if(response == "Logged in")
            {
                window.location.href = "homepage.html";
            }
            console.log(response);
            return response;
        });
}

function pushUser(username, password, verifypassword) {
    $.ajax({
        method: "POST",
        url: "Data.php",
        data: { 
            function: "pushUser",
            username: username,
            password: password,
            verifypassword: verifypassword,
        }
      })
        .done(function( response ) {
            console.log(response);
        });
}

const selectPrompt = (promptId) => {
    fetch(`http://localhost:8000/composite_prompts/${promptId}/expanded`)
        .then(response => response.json())
        .then(prompt => {
            promptTextarea.value = prompt.fragments.reduce((acc, fragment) => {
                return `${acc} \n\n${fragment.content}`;
            }, '');
        });
}

fetch(`http://localhost:8000/composite_prompts`)
    .then(response => response.json())
    .then(composite_prompts => {
        for (const composite_prompt of composite_prompts) {
            const template = promptTemplate.content.cloneNode(true);
            template.querySelector('h2').innerText = composite_prompt.title;
            template.querySelector('p').innerText = composite_prompt.description;
            template.querySelector('button').addEventListener('click', () => {selectPrompt(composite_prompt.id)});
            promptOptionsDisplay.appendChild(template);
        }
    });

if (saveButton !== null) {
    saveButton.addEventListener('click', () => {
        pushPrompt(promptTextarea.value, category.value, use_case.value);
    })
}

if(loginButton !== null)
{
    loginButton.addEventListener('click', () => {
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const output = checkIfUser(username, password);
        console.log(output);
    });
}

if(registerButton !== null)
{
    registerButton.addEventListener('click', () => {
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const verifypassword = document.getElementById("verifypassword").value;
        pushUser(username, password, verifypassword);
    });
}

if(askButton !== null)
{   
    askButton.addEventListener('click', () => {
        window.location.href = `https://chat.openai.com/?q=${promptTextarea.value}`;
    });
}

if(saveButton !== null)
{
    saveButton.addEventListener('click', async () => {
        const newPrompt = await fetch(`http://localhost:8000/composite_prompts`, {
            method: 'POST',
            body: JSON.stringify({
                "author_id": 1,
                "title": "New Prompt",
                "description": "default description"
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                return data;
            });
        const newFragment = await fetch(`http://localhost:8000/prompt_fragments`, {
            method: 'POST',
            body: JSON.stringify({
                "author_id": 1,
                "content": promptTextarea.value,
                "description": "default description fragment",
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                return data
            });
    
        fetch(`http://localhost:8000/composite_prompts/${newPrompt.id}/fragments/${newFragment.id}`, {
            method: 'POST',
            body: JSON.stringify({
                "order_index": 0
            })
        });
    });
}
const navbar = document.getElementById("navbar");
let lastScrollY = window.scrollY;

window.addEventListener("scroll", () => {
  const currentScrollY = window.scrollY;

  if (currentScrollY > lastScrollY) {
    // Scrolling down
    navbar.classList.add("transparent");
  } else {
    // Scrolling up
    navbar.classList.remove("transparent");
  }

  lastScrollY = currentScrollY;
});

// Check the current URL and decide category-based rendering
// Check the current URL and decide category-based rendering
const categories = {
    "math.html": "math",
    "geography.html": "geography",
    "english.html": "english",
    "dutch.html": "dutch",
    "coding.html": "coding",
    "economics.html": "economics",
    "history.html": "history"
};

const currentPage = Object.keys(categories).find(page => window.location.href.includes(page));

if (currentPage) {
    const currentCategory = categories[currentPage];
    console.log(`Current category detected: ${currentCategory}`);

    getPrompts()
        .then(output => {
            console.log('Raw output:', output);
            output = JSON.parse(output); // Parse the fetched prompts
            console.log('Parsed output:', output);

            const filteredOutput = output.filter(item => item.category === currentCategory);
            console.log(`Filtered prompts for ${currentCategory}:`, filteredOutput);

            if (filteredOutput.length > 0) {
                createTestSection('cardContainer', filteredOutput);
            } else {
                console.warn(`No prompts found for category: ${currentCategory}`);
            }
        })
        .catch(error => {
            console.error('Error fetching prompts:', error);
        });
} else {
    console.warn('No matching category found for this page.');
}

// Function to dynamically create a test section with filtered prompts
function createTestSection(cardContainerId, output) {
    const cardContainer = document.getElementById(cardContainerId);

    if (!cardContainer) {
        console.error(`Card container with ID "${cardContainerId}" not found.`);
        return;
    }

    // Clear the card container before adding new content
    cardContainer.innerHTML = '';

    // Loop through filtered output to create cards for each prompt
    output.forEach((item, index) => {
        const section = document.createElement("section");
        section.id = `tests`;

        const heading = document.createElement("h3");
        heading.textContent = item.title || ` ${item.use_case}`;
        section.appendChild(heading);

        const promptTextContainer = document.createElement("div");
        promptTextContainer.id = `prompt-text-container`;

        const chatbutton = document.createElement("button")
        chatbutton.id = "askChatGPT"
        const chatimg = document.createElement("img");
        chatimg.src = "icons8-chatgpt-48.png";
        chatimg.id = "chatimg"

        const textarea = document.createElement("textarea");
        textarea.name = `prompt-${index}`;
        textarea.id = `prompt`;
        textarea.disabled = "true"
        textarea.style.width = "65.3%";
        textarea.rows = 20;
        textarea.textContent = item.prompt || "No prompt available";

        section.appendChild(promptTextContainer);
        section.appendChild(textarea);
        promptTextContainer.appendChild(chatbutton)
        chatbutton.appendChild(chatimg)

        cardContainer.appendChild(section);
    });
}
