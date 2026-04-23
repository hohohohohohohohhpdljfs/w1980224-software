//mycode
import { useEffect, useMemo, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import "./habits.css";

export default function Habits() {
     
    const navigate = useNavigate();

    //stores all habits loaded from the backend 
    const [habits, setHabits] = useState([]);
    
    //stores new habit form input values 
    const [title, setTitle] = useState("");
    const [category, setCategory] = useState("Health & Fitness");
    const [reminderTime, setReminderTime] = useState("");

    //stores editing state for a habit 
    const [editingId, setEditingId] = useState(null);
    const [editTitle, setEditTitle] = useState("");
    const [editCategory, setEditCategory] = useState("Health & Fitness");

    //keeps track of reminders already shown today
    const notifiedTodayRef = useRef({});

    //stores the currently active reminder banner
    const [activeReminder, setActiveReminder] = useState(null);

    //load habits from the API and formats the data for the page 
    async function loadHabits() {
        const res = await fetch("/habitlilli/api/habits.php", {
            credentials: "include"
        });
        const data = await res.json();

        const habitsFromApi = (data.habits || []).map((habit) => ({
            ...habit,
            category: habit.category || "Health & Fitness",
            reminder_time: habit.reminder_time || "",
            done_today: !!habit.done_today,
            streak: Number(habit.streak ?? 0),
            weekly_count: Number(habit.weekly_count ?? 0),
            done_yesterday: !!habit.done_yesterday,
            prediction: habit.prediction || "Too early to predict",
            target: 1,
            progress: Number(habit.progress ?? 0)
        }));

        setHabits(habitsFromApi);
    }
    
    //loads habit when the page first opens
    useEffect(() => {
        loadHabits();
     }, []);
     
     //asks the browser for notification permission
     useEffect(() => {
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }
     }, []);

     //shows a broswer notification for habit reminder
     function showReminderNotification(habitTitle) {
        console.log("showRemindersNotification called for:", habitTitle);
        console.log("Notification permission is:", Notification.permission);
        
        if (!("Notification" in window)) return;
        if (Notification.permission !== "granted") return;
        
        new Notification("Habit Reminder", {
            body: `You haven't completed "${habitTitle}" today yet.`
        
        });
     }

     //hides the reminder banner
     function dismissReminder() {
        setActiveReminder(null);
     }

     //checks every 30 seconds. whether a reminder should appear
     useEffect(() => {
        const interval = setInterval(() => {
            const now = new Date();

            const currentTime =
            now.getHours().toString().padStart(2, "0") +
            ":" +
            now.getMinutes().toString().padStart(2, "0")

            const todayKey = 
            now.getFullYear() +
            "-" +
            (now.getMonth() + 1).toString().padStart(2, "0") +
            "-" +
             now.getDate().toString().padStart(2, "0");
            
             habits.forEach((habit) => {
                if (!habit.reminder_time) return;
                if (habit.done_today) return;

                const reminder = String(habit.reminder_time).slice(0, 5);
                const uniqueKey = `${habit.id}-${todayKey}`;
                
                console.log("Checking habit:", {
                    title: habit.title,
                    reminder,
                    currentTime,
                    done: habit.done_today,
                    uniqueKey
                });
                if (
                    reminder === currentTime && 
                    !notifiedTodayRef.current[uniqueKey]
                ) {
                    showReminderNotification(habit.title);

                    setActiveReminder(habit.title);

                    notifiedTodayRef.current[uniqueKey] = true;
                }
             });
        
          }, 30000);

         return () => clearInterval(interval);
     }, [habits]);

   //counts how many habits are completed today
    const completedToday = useMemo(
        () => habits.filter((habit) => habit.done_today).length,
        [habits]
    );

    //adds together all streak values
    const totalStreaks = useMemo(
        () => habits.reduce((sum, habit) => sum + habit.streak, 0),
        [habits]
    );
    
//adds a new habit
     async function addHabit(e) {
        e.preventDefault();
        if (!title.trim()) return;
      
        //temporary habit shown instantly before backend saves it 
        const tempHabit = {
            id: Date.now(),
            title: title,
            category: category,
            reminder_time: reminderTime,
            done_today: false,
            streak: 0,
            weekly_count: 0,
            done_yesterday: false,
            prediction: "Too early to predict",
            target: 1,
            progress: 0
        };

        setHabits((prev) => [tempHabit, ...prev]);

        const savedTitle = title;
        const savedCategory = category;
        const savedReminderTime = reminderTime;

        setTitle("");
        setCategory("Health & Fitness");
        setReminderTime("");

       try { 
        const res = await fetch("/habitlilli/api/habits.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify({
                title: savedTitle,
                frequency: "daily",
                category: savedCategory,
                reminder_time: savedReminderTime
            })
        });
        
        const data = await res.json();
        console.log("Backend response:", data);

        if (!res.ok || data.error) {
            throw new Error(data.error || "Failed to save habit");
        }
       
        //reloads the correct saved data from backend
       await loadHabits();
    } catch (error) {
        console.error("Habit save failed", error);
        alert("Habit save failed. Check console.");

        //remove the temporary habit if saving failed
        setHabits((prev) => prev.filter((h) => h.id !== tempHabit.id));
    }
     }

     //marks habit as done or undone
    async function toggleDone(id) {
        const clickedHabit = habits.find((habit) => habit.id === id);
        if (!clickedHabit) return;

        const wasDone = clickedHabit.done_today;

        //updates the page immediately before saving 
        setHabits((prev) =>
            prev.map((habit) =>
               habit.id === id
                ? {
                    ...habit,
                    done_today: !wasDone,
                    progress: wasDone ? 0 : habit.target,
                }
                : habit
           )
        );
        
        //hides reminder banner if the habit get completed
        if (!wasDone && activeReminder === clickedHabit.title) {
            setActiveReminder(null);
        }

        try {
            const res = await fetch("/habitlilli/api/habit_complete.php", {
            method: wasDone ? "DELETE" : "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "include",
            body: JSON.stringify({
                habit_id: id
        } )
    });

        const data = await res.json();
        console.log("Habit completion saved:", data);

        if (!res.ok || data.error) {
            throw new Error(data.error || "Failed to complete habit");
        }

        //removes reminder tracking if habit was unchecked
        if (wasDone) {
            const now = new Date();
            const todayKey =
            now.getFullYear() +
            "-" +
            (now.getMonth() + 1).toString().padStart(2, "0") +
            "-" +
            now.getDate().toString().padStart(2, "0");
            delete notifiedTodayRef.current[`${id}-${todayKey}`];
        }

        await loadHabits();
   } catch (error) {
        console.error("Habit save failed", error);
        await loadHabits();
   }
  }
      
  //increase habit progress using the + button
      async function increaseProgress(id) {
        const clickedHabit = habits.find((habit) => habit.id === id);
        if (!clickedHabit) return;

        const newProgress = Math.min(clickedHabit.progress + 1, clickedHabit.target);
        const shouldBeDone = newProgress >= clickedHabit.target;

        setHabits((prev) =>
            prev.map((habit) =>
                habit.id === id
            ? {
                ...habit,
                progress: newProgress,
                done_today: shouldBeDone,
            }
            : habit
       )
    );
     
    //saves completion when progress reaches the target 
    if (shouldBeDone && !clickedHabit.done_today) {
        if (activeReminder === clickedHabit.title) {
            setActiveReminder(null);
        }
       
        try {
            const res = await fetch("/habitlilli/api/habit_complete.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                    credentials: "include",
                    body: JSON.stringify({
                    habit_id: id,
                }),
            });

        const data = await res.json();

        if (!res.ok || data.error) {
            throw new Error(data.error || "Failed to complete habit");
        }

        await loadHabits();
   } catch (error) {
        console.error("Increase progress save failed:", error);
        await loadHabits();
        }
    }
   }

       //decreases habit progress using the - button
       async function decreaseProgress(id) {
        const clickedHabit = habits.find((habit) => habit.id === id);
        if (!clickedHabit) return;

        const newProgress = Math.max(clickedHabit.progress - 1, 0);
        const shouldBeDone = newProgress >= clickedHabit.target;

         setHabits((prev) =>
            prev.map((habit) =>
                habit.id === id
            ? {
                ...habit,
                progress: newProgress,
                done_today: shouldBeDone,
            }
            : habit
         )
        );
        
        //removes completion if progress below target 
        if (clickedHabit.done_today && !shouldBeDone) {
       
        try {
            const res = await fetch("/habitlilli/api/habit_complete.php", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                },
                    credentials: "include",
                    body: JSON.stringify({
                    habit_id: id,
                }),
            });

            const data = await res.json();

            if (!res.ok || data.error) {
            throw new Error(data.error || "Failed to UNcomplete habit");
         }

            const now = new Date();
            const todayKey =
            now.getFullYear() +
            "-" +
            (now.getMonth() + 1).toString().padStart(2, "0") +
            "-" +
            now.getDate().toString().padStart(2, "0");
            delete notifiedTodayRef.current[`${id}-${todayKey}`];
    
            await loadHabits();
            } catch (error) {
            console.error("Decrease progress save failed:", error);
            await loadHabits();
        } 
    }
}
      //opens edit mode for a habit
      function startEdit(habit) {
        setEditingId(habit.id);
        setEditTitle(habit.title);
        setEditCategory(habit.category || "Health & Fitness");
      }
    
    
       //cancels editing mode
        function cancelEdit() {
              setEditingId(null);
              setEditTitle("");
              setEditCategory("Health & Fitness");
            }
            
            //saves edited habit details
            async function saveEdit(id) {

                if (!editTitle.trim()) return;

                const oldHabits = habits;
                
                //updates the page immediately before backend save 
                setHabits((prev) =>
                    prev.map((habit) =>
                        habit.id === id
                        ? {
                            ...habit,
                            title: editTitle.trim(),
                            category: editCategory
                        }
                        : habit
                    )
                );

        try {
            const res= await fetch("/habitlilli/api/habits.php", {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    id: id,
                    title: editTitle.trim(),
                    category: editCategory,
                    frequency: "daily"
                })
            });

            const data = await res.json();
            console.log("Edit response:", data);

            if (!res.ok || data.error) {
                throw new Error(data.error || "Failed to update habit");
            }

            setEditingId(null);
            setEditTitle("");
            setEditCategory("Health & Fitness");
            await loadHabits();

        } catch (error) {
            console.error("Edit failed:", error);
            alert("Could not update habit.");
            setHabits(oldHabits);
          }
         }
              //deletes a habit
              async function deleteHabit(id) {
                  const oldHabits = habits;
                
                //removes habit from page immediately
                setHabits((prev) => prev.filter((habit) => habit.id !== id));
        
                 try {
                    const res = await fetch("/habitlilli/api/habits.php", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    credentials: "include",
                    body: JSON.stringify({ id: id})
                });

                const data = await res.json();
                console.log("Delete response:", data);
                console.log("Delete status:", res.status);

                if (!res.ok || data.error) {
                    throw new Error(data.error || "Failed to delete habit");
                }
                await loadHabits()

            } catch (error) {
                console.error("Delete failed:", error);
                alert("Could not delete habit.");

                //restores habit if delete fails
                setHabits(oldHabits);
            }
        }

        return (
            <div className="daily-page">
                <div className="daily-shell">
                    <div className="page-top">
                       <div>
                        <h1 className="page-title">Habit Tracker</h1>
                        <p className="page-subtitle">
                            Create habits, track progress and stay consistent.
                        </p>
                       </div>
           
                       <button
                        className="back-dashboard-btn"
                        onClick={() =>
                            (window.top.location.href = 
                                "/habitlilli/dashboard.php")
                            }
                    
                     >
                        Back to Dashboard
                    </button>
                </div>

                    <div className="view-switch">
                        <button className="view-tab active">Daily view</button>

                        <button
                        className="view-tab"
                        onClick={() => navigate("/calendar")}
                        >
                            Calendar view
                        </button>
                    </div>

                    <div className="tracker-board">
                    <div className="daily-header">
                        <div>
                            <h1>Daily Habits</h1>
                        </div>

            
            </div>


            <form className="habit-input-row" onSubmit={addHabit}>
                <input
                type="text"
                placeholder="Enter a new habit..."
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                />

                <select
                 value={category} 
                onChange={(e) => setCategory(e.target.value)}
                >

                <option>Health & Fitness</option>
                <option>Wellness</option>
                <option>Study</option>
                <option>Productivity</option>
                </select>
                
                <input 
                    type="time"
                    value={reminderTime}
                    onChange={(e) => setReminderTime(e.target.value)}
                />

                <button type="submit" className="add-btn">+</button>
                </form>

                {activeReminder && (
                    <div
                       style={{
                        position: "fixed",
                        top: "20px",
                        left: "50%",
                        transform: "translateX(-50%)",
                        zIndex: 9999,

                        background: "#f5efe6",
                        border: "1px solid #e7ddd0",
                        borderRadius: "14px",
                        padding: "12px 16px",
                        display: "flex",
                        gap: "10px",
                        flexWrap: "wrap"
                       }}
                    >
                    <span> Reminder: {activeReminder}</span>

                    <div style={{ display: "flex", gap: "10px" }}>
                        <button
                               type="button"
                               onClick={() => navigate("/tracker")}
                               style={{
                                background: "#111",
                                color: "#fff",
                                border: "none",
                                borderRadius: "10px",
                                padding: "8px 12px",
                                cursor: "pointer"
                               }}
                        >
                            Open Tracker
                        </button>

                        <button
                              type="button"
                              onClick={dismissReminder}
                              style={{
                                background: "#e9e2d8",
                                border: "none",
                                borderRadius: "10px",
                                padding: "8px 12px",
                                cursor: "pointer"
                              }}
                        >
                            Dismiss
                        </button>
                    </div>
                    </div>
                )}
                
                <div className="content-grid">
                <div className="habits-stack">
                    {habits.map((habit) => {
                        const percent = habit.target
                        ? Math.round((habit.progress / habit.target) * 100)
                         : 0;

                         return (
                            <div className="habit-panel" key={habit.id}>
                                <div className="habit-top-row">
                                    <div className="habit-left">
                                        <button
                                        type="button"
                                        className="mini-btn"
                                        onClick={() => decreaseProgress(habit.id)}
                                    >
                                     -
                                    </button>

                                    <div className="progress-fraction">
                                        {habit.progress}/{habit.target}
                                    </div>
                                
                                <button 
                                 type="button"
                                 className="mini-btn"
                                 onClick={() => increaseProgress(habit.id)}
                                 >
                                    +
                                 </button>
                             
                             <div className="habit-text">
                                {editingId === habit.id ? (
                                <>

                                <input 
                                   type="text"
                                   value={editTitle}
                                   onChange={(e) => setEditTitle(e.target.value)}
                                />

                                <select 
                                value={editCategory}
                                onChange={(e) => setEditCategory(e.target.value)}
                                >
                                <option>Health & Fitness</option>
                                <option>Wellness</option>
                                <option>Study</option>
                                <option>Productivity</option>
                                </select>

                                <div className="habit-subline">
                                <button
                                type="button"
                                className="small-icon"
                                onClick={() => saveEdit(habit.id)}
                                >
                                Save
                                </button>

                                <button
                                type="button"
                                className="small-icon danger"
                                onClick={cancelEdit}
                                >
                                Cancel
                                </button>
                                </div>
                                </>
                                ) : (
                                <>
                                
                                <h3>{habit.title}</h3>

                                <div className="habit-subline">
                                    <span>Streak: {habit.streak} days 🔥</span>
                                    <span className="tag">{habit.category}</span>
                                    <span>
                                        {habit.reminder_time
                                        ? `Reminder: ${String(habit.reminder_time).slice(0, 5)}`
                                        : "No reminder"} 
                                    </span>
                                    <span
                                        className={`prediction-tag ${habit.prediction
                                            .toLowerCase()
                                            .replace(/\s+/g, "-")}`}
                                        
                                    >
                                       Prediction: {habit.prediction} 
                                                
                                    </span>
                                    </div>
                                </>
                                )}
                                </div>
                             </div>
                                
                             <div className="habit-right">
                                <button
                                type="button"
                                className={`check-btn ${habit.done_today ? "done" : ""}`}
                                onClick={() => toggleDone(habit.id)}
                               >
                                {habit.done_today ? "✓" : ""}
                            </button>

                            <button 
                            type="button"
                            className="small-icon"
                            onClick={() => startEdit(habit)}
                            >
                            ✎
                            </button>
                             
                            <button
                            type="button"
                            className="small-icon danger"
                            onClick={() => deleteHabit(habit.id)}
                            >
                             🗑
                              </button>
                             </div>
                            </div>
                           
                            <div className="progress-line">
                                <div
                                className="progress-line-fill"
                                style={{ width: `${percent}%` }}
                                />
                            </div>
                            </div>
                             );
                            })}
                        </div>

                        <div className="summary-card summary-bottom">
                            <h2>Progress Summary</h2>
                    
                            
                            <div className="summary-grid">
                                <div className="summary-box">
                                    <p>Completed today</p>
                                    <strong>
                                        {completedToday}/{habits.length}
                                    </strong>
                                    </div>

                                    <div className="summary-box">
                                        <p>Total Streaks</p>
                                        <strong>{totalStreaks}</strong>
                                </div>
                             </div>
                         </div>
                    </div>

                </div>
            </div>
        </div>
        );
    }