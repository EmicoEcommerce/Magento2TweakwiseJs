# Review Merge Request

Review a GitLab merge request using `glab`, show findings in chat, then post approved comments.

## Usage

```
/review-merge-request <MR-number or MR-URL>
```

## Steps

1. **Fetch MR info**: Run `glab mr view <MR>` to get title, description, author, branch info.
2. **Fetch the diff**: Run `glab mr diff <MR>` to get all changed files and their diffs.
3. **Review**: Analyse the diff thoroughly. For each finding, produce two versions:
   - A **Dutch** version to show in the chat to the user.
   - An **English** version to post on GitLab (prepared but not shown unless posting).

   Structure each finding as:

```
[N] <file>:<line>
Type: Bug | Security | Style | Performance | Suggestion
Severity: Critical | Major | Minor | Info
---
<uitleg in het Nederlands>

Suggestie:
<concrete fix of code snippet>
```

4. **Present findings**: Show all findings numbered in the chat **in Dutch**. After listing them, ask:
   > "Welke punten wil je als comment plaatsen op de MR? Geef de nummers op (bijv. 1,3,5) of zeg 'alles' of 'geen'."

5. **Post approved comments**: For each approved finding, post the **English** version on GitLab:
   ```
   glab mr note <MR> --message "<comment text>"
   ```
   Format the English comment as: `**[Type — Severity]** <file>:<line>\n\n<explanation in English>\n\n<suggested fix in English>`

6. **Confirm**: Report which comments were successfully posted.

## Notes

- Always fetch fresh data with `glab`; never rely on cached or assumed MR state.
- If `glab` is not authenticated, tell the user to run `glab auth login`.
- The MR argument can be a number (e.g. `42`) or a full URL (e.g. `https://git.emico.io/.../-/merge_requests/42`).
- Extract the MR number from a URL if needed before passing it to `glab` commands.
