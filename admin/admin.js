(() => {
    const form = document.getElementById("content-form");
    const saveBtns = document.querySelectorAll(".save-btn");
    const dirtyNote = document.getElementById("dirty-note");
    const navLinks = [...document.querySelectorAll(".studio-nav nav a[href^='#']")];
    const sections = [...document.querySelectorAll(".section[id], #overview")];
    let dirty = false;

    const markDirty = () => {
        if (dirty) {
            return;
        }
        dirty = true;
        saveBtns.forEach((btn) => btn.classList.add("is-dirty"));
        if (dirtyNote) {
            dirtyNote.textContent = "Unsaved changes";
        }
    };

    const nextIndex = (list) => {
        const nums = [...list.querySelectorAll("[name]")].map((el) => {
            const match = String(el.getAttribute("name") || "").match(/\[(\d+)\]/);
            return match ? Number(match[1]) : -1;
        });
        return nums.length ? Math.max(...nums) + 1 : 0;
    };

    const templates = {
        stats: (idx) => `<article class="repeat-card">
            <div class="repeat-head"><strong>Stat</strong>
            <button class="btn btn-danger" type="button" data-remove>Remove</button></div>
            <div class="grid-2">
                <label class="field"><span>Value</span><input name="stats[${idx}][value]" placeholder="7+"></label>
                <label class="field"><span>Label</span><input name="stats[${idx}][label]" placeholder="Years experience"></label>
            </div>
        </article>`,
        focus: (idx) => `<article class="repeat-card">
            <div class="repeat-head"><strong>Focus card</strong>
            <button class="btn btn-danger" type="button" data-remove>Remove</button></div>
            <div class="grid-2">
                <label class="field"><span>Kicker</span><input name="focus[${idx}][kicker]" placeholder="Cloud"></label>
                <label class="field"><span>Title</span><input name="focus[${idx}][title]" placeholder="Cloud Engineer"></label>
            </div>
            <p class="kicker">Icon</p>
            <div class="icon-pick">
                <label><input type="radio" name="focus[${idx}][icon]" value="cloud" checked> Cloud</label>
                <label><input type="radio" name="focus[${idx}][icon]" value="repeat"> Delivery</label>
                <label><input type="radio" name="focus[${idx}][icon]" value="layers"> Platform</label>
                <label><input type="radio" name="focus[${idx}][icon]" value="terminal"> Automation</label>
            </div>
            <label class="field"><span>Text</span><textarea name="focus[${idx}][text]" rows="3"></textarea></label>
        </article>`,
        jobs: (idx) => `<article class="repeat-card">
            <div class="repeat-head"><strong>Role</strong>
            <button class="btn btn-danger" type="button" data-remove>Remove</button></div>
            <div class="grid-2">
                <label class="field"><span>Role</span><input name="jobs[${idx}][role]" placeholder="Senior DevOps Engineer"></label>
                <label class="field"><span>Company</span><input name="jobs[${idx}][company]"></label>
                <label class="field"><span>Location</span><input name="jobs[${idx}][location]"></label>
                <label class="field"><span>Period</span><input name="jobs[${idx}][period]" placeholder="Jan 2025 – Present"></label>
            </div>
            <label class="field"><span>Logo path</span><input name="jobs[${idx}][logo]" placeholder="companies/jio.png"></label>
            <div class="toggle-row">
                <label class="toggle"><input type="checkbox" name="jobs[${idx}][current]" value="1"><span class="toggle-ui"></span><span>Current role</span></label>
                <label class="toggle"><input type="checkbox" name="jobs[${idx}][logo_wide]" value="1"><span class="toggle-ui"></span><span>Wide logo</span></label>
            </div>
            <label class="field"><span>Highlights</span><textarea name="jobs[${idx}][points]" rows="4" placeholder="One achievement per line"></textarea></label>
        </article>`,
        groups: (idx) => `<article class="repeat-card">
            <div class="repeat-head"><strong>Skill group</strong>
            <button class="btn btn-danger" type="button" data-remove>Remove</button></div>
            <div class="grid-2">
                <label class="field"><span>Group</span><input name="skill_groups[${idx}][name]" placeholder="Cloud"></label>
                <label class="field"><span>Items</span><input name="skill_groups[${idx}][items]" placeholder="AWS, Azure, GCP"></label>
            </div>
        </article>`,
        logos: (idx) => `<article class="repeat-card">
            <div class="repeat-head"><strong>Logo</strong>
            <button class="btn btn-danger" type="button" data-remove>Remove</button></div>
            <div class="grid-2">
                <label class="field"><span>Name</span><input name="skill_logos[${idx}][name]" placeholder="Docker"></label>
                <label class="field"><span>File</span><input name="skill_logos[${idx}][file]" placeholder="skills/docker.svg"></label>
            </div>
        </article>`,
    };

    const lists = {
        stats: "stats-list",
        focus: "focus-list",
        jobs: "jobs-list",
        groups: "groups-list",
        logos: "logos-list",
    };

    document.querySelectorAll("[data-add]").forEach((btn) => {
        btn.addEventListener("click", () => {
            const key = btn.getAttribute("data-add");
            const list = document.getElementById(lists[key]);
            if (!list || !templates[key]) {
                return;
            }
            const idx = nextIndex(list);
            list.insertAdjacentHTML("beforeend", templates[key](idx));
            const card = list.lastElementChild;
            card?.querySelector("input, textarea")?.focus();
            card?.scrollIntoView({ behavior: "smooth", block: "center" });
            markDirty();
        });
    });

    document.addEventListener("click", (event) => {
        const btn = event.target.closest("[data-remove]");
        if (!btn) {
            return;
        }
        btn.closest(".repeat-card")?.remove();
        markDirty();
    });

    form?.addEventListener("input", markDirty);
    form?.addEventListener("change", markDirty);
    form?.addEventListener("submit", () => {
        dirty = false;
    });

    window.addEventListener("beforeunload", (event) => {
        if (!dirty) {
            return;
        }
        event.preventDefault();
        event.returnValue = "";
    });

    document.addEventListener("keydown", (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "s") {
            event.preventDefault();
            form?.requestSubmit();
        }
    });

    document.querySelectorAll(".photo-tile input[type='file']").forEach((input) => {
        input.addEventListener("change", () => {
            const file = input.files?.[0];
            const tile = input.closest(".photo-tile");
            const frame = tile?.querySelector(".photo-frame");
            const remove = tile?.querySelector(".photo-remove input");
            if (remove) {
                remove.checked = false;
            }
            if (!file || !frame) {
                return;
            }
            const url = URL.createObjectURL(file);
            frame.innerHTML = `<img src="${url}" alt="New photo preview">`;
            tile?.classList.remove("is-empty");
        });
    });

    document.querySelectorAll("[data-count]").forEach((area) => {
        const output = document.getElementById(area.getAttribute("data-count"));
        const update = () => {
            if (output) {
                output.textContent = `${area.value.length} characters`;
            }
        };
        area.addEventListener("input", update);
        update();
    });

    document.querySelectorAll("[data-password]").forEach((btn) => {
        btn.addEventListener("click", () => {
            const input = document.getElementById(btn.getAttribute("data-password"));
            if (!input) {
                return;
            }
            const hidden = input.type === "password";
            input.type = hidden ? "text" : "password";
            btn.setAttribute("aria-label", hidden ? "Hide password" : "Show password");
        });
    });

    navLinks.forEach((link) => {
        link.addEventListener("click", (event) => {
            const id = (link.getAttribute("href") || "").slice(1);
            const target = document.getElementById(id);
            if (!target) {
                return;
            }
            event.preventDefault();
            target.scrollIntoView({ behavior: "smooth", block: "start" });
            history.replaceState(null, "", `#${id}`);
            navLinks.forEach((item) => item.classList.toggle("is-active", item === link));
        });
    });

    const spy = () => {
        let current = "overview";
        sections.forEach((section) => {
            if (section.getBoundingClientRect().top <= 140) {
                current = section.id;
            }
        });
        navLinks.forEach((link) => {
            link.classList.toggle("is-active", link.getAttribute("href") === `#${current}`);
        });
    };
    window.addEventListener("scroll", spy, { passive: true });
    spy();
})();
