import Ajax from "core/ajax";

export const init = () => {
  const elements = document.querySelectorAll(
    ".block_learning_session-reset_password"
  );

  elements.forEach((element) => {
    element.addEventListener("click", () => {
      const requests = Ajax.call([
        {
          methodname: "block_learning_session_reset_password",
          args: {
            userid: parseInt(element.dataset.userid)
          }
        }
      ]);

      requests[0].then((password) => {
        element.closest("tr").querySelector("td:nth-child(3)").textContent =
          password;
      });
    });
  });
};
