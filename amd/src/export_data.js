import Ajax from "core/ajax";
import Notification from "core/notification";

export const init = (courseId, sessionCode, userId) => {
  const element = document.getElementById(
    "block_learning_session-request_data"
  );

  if (!element) {
    return;
  }

  element.addEventListener("click", () => {
    const requests = Ajax.call([
      {
        methodname: "block_learning_session_export_data",
        args: {
          courseid: courseId,
          sessioncode: sessionCode,
          userid: userId
        }
      }
    ]);

    requests[0].catch(Notification.exception);
  });
};
