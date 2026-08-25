(() => {
    const header = document.getElementById("site-header");
    const form = document.getElementById("contact-form");
    const status = document.getElementById("form-status");
    const themeToggle = document.getElementById("theme-toggle");
    const roleEl = document.getElementById("role-rotate");
    const roles = Array.isArray(window.__ROLES__) ? window.__ROLES__ : [];
    const navLinks = [...document.querySelectorAll(".site-nav a[href^='#'], .bottom-nav a[href^='#']")];
    const sections = [...document.querySelectorAll("section[id]")];

    window.addEventListener("scroll", () => {
        header?.classList.toggle("is-scrolled", window.scrollY > 8);
    }, { passive: true });

    const applyTheme = (theme) => {
        document.documentElement.setAttribute("data-theme", theme);
        const color = theme === "light" ? "#f4f6fb" : "#0d0e12";
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) {
            meta.setAttribute("content", color);
        }
        localStorage.setItem("theme", theme);
    };

    themeToggle?.addEventListener("click", () => {
        const next = document.documentElement.getAttribute("data-theme") === "light" ? "dark" : "light";
        applyTheme(next);
    });

    if (roleEl && roles.length > 1) {
        let index = 0;
        setInterval(() => {
            index = (index + 1) % roles.length;
            roleEl.style.opacity = "0";
            setTimeout(() => {
                roleEl.textContent = roles[index];
                roleEl.style.opacity = "1";
            }, 180);
        }, 2600);
        roleEl.style.transition = "opacity 0.18s ease";
    }

    const spy = () => {
        const y = window.scrollY + 140;
        let current = "about";
        sections.forEach((section) => {
            if (section.offsetTop <= y) {
                current = section.id;
            }
        });
        if (current === "stats" || current === "doing") {
            current = "about";
        } else if (current === "photos") {
            current = "skills";
        }
        navLinks.forEach((link) => {
            const href = link.getAttribute("href") || "";
            link.classList.toggle("is-active", href === `#${current}`);
        });
    };

    window.addEventListener("scroll", spy, { passive: true });
    spy();

    const revealItems = document.querySelectorAll("[data-reveal]");
    if ("IntersectionObserver" in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add("is-visible");
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: "0px 0px -36px 0px" });
        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add("is-visible"));
    }

    form?.addEventListener("submit", async (event) => {
        event.preventDefault();
        status.textContent = "Sending…";
        status.classList.remove("is-error");

        try {
            const response = await fetch(form.action, {
                method: "POST",
                body: new FormData(form),
            });
            const data = await response.json();

            if (data.ok) {
                status.textContent = data.message;
                form.reset();
                return;
            }

            if (data.fallback && data.mailto) {
                status.textContent = data.message;
                window.location.href = data.mailto;
                return;
            }

            status.textContent = data.message || "Could not send the message.";
            status.classList.add("is-error");
        } catch (error) {
            status.textContent = "Could not send. Please use email or WhatsApp.";
            status.classList.add("is-error");
        }
    });
})();
