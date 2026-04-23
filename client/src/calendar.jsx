//mycode
import { useState, useEffect, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import "./calendar.css";


export default function Calendar() {
    const navigate = useNavigate();

    //stores the month currently being shown
    const [currentMonth, setCurrentMonth] = useState(new Date());
    //stores the data the user clicked on
    const [selectedDate, setSelectedDate] = useState(new Date());
    //stores habits for the selected day
    const [dayHabits, setDayHabits] = useState([]);
    //stores completion data for the whole month 
    const [monthData, setMonthData] = useState([]);

//formats month and year for the calendar heading
    function formatMonthYear(date) {
        return date.toLocaleDateString("en-GB", {
            month: "long",
            year: "numeric",
        });
    }

    //formats a full date as YYYY-MM-DD for the API
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    }

    //formats just year and month for monthly API requests
    function formatMonth(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() +1).padStart(2, "0");
        return `${year}-${month}`;
    }

    //moves the calendar backward or forward by one month
    function changeMonth(direction) {
        setCurrentMonth((prev) => {
            const next = new Date(prev);
            next.setMonth(prev.getMonth() + direction);
            return next;
        });
    }
    //builds the calendar grid for the current month 
    function getDaysInMonth(date) {
        const year = date.getFullYear();
        const month = date.getMonth();

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        const days = [];
        const startDay = firstDay.getDay();

        //adds empty spaces before the first real day
        for (let i =0; i < startDay; i++) {
            days.push(null);
        }

        //adds every day in the month
        for (let d = 1; d <= lastDay.getDate(); d++) {
            days.push(new Date(year, month, d));
        }

        return days;
    }

    //checks if two dates are the same day
    function isSameDay(a, b) {
        return (
            a.getFullYear() === b.getFullYear() &&
            a.getMonth() === b.getMonth() &&
            a.getDate() === b.getDate() 
        );
    }
        
        //stop users from selecting future dates 
        function isFutureDay(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const checkDate = new Date(date);
            checkDate.setHours(0, 0, 0, 0)

            return checkDate > today;
        }
    
    //loads all habits for selected day
    async function loadDayHabits(date) {
        try {
            const res = await fetch(
              `/habitlilli/api/calendar.php?type=day&date=${formatDate(date)}`,
            {
                credentials: "include"
            }
        );

        const data = await res.json();
        
        if (!res.ok || data.error) {
            throw new Error(data.error || "Failed to load day habits");
        }

        setDayHabits(data.habits || []);
        } catch (error) {
            console.error("Failed to load day habits:", error);
            setDayHabits([]);
        }
    }

    //loads monthly completion data for the calendar
    async function loadMonthData(date) {
        try {
            const res = await fetch(
              `/habitlilli/api/calendar.php?type=month&month=${formatMonth(date)}`,
            {
                credentials: "include"
            }
        );

        const data = await res.json();
        
        if (!res.ok || data.error) {
            throw new Error(data.error || "Failed to load month data");
        }

        setMonthData(data.days || []);
        } catch (error) {
            console.error("Failed to load month data:", error);
            setMonthData([]);
        }
    }

    //reload day habits whenever the selected date changes
    useEffect(() => {
        loadDayHabits(selectedDate);
   }, [selectedDate]);

   //reload month data whenever the calendar month changes
    useEffect(() => {
        loadMonthData(currentMonth);
    }, [currentMonth]);

    //creates a quick lookup of dates with completions
    const completedDates = useMemo(() => {
        return new Set(monthData.map((item) => item.log_date));
    }, [monthData]);

    //counts how many habits were completed on the selected day
    const completedCount = dayHabits.filter(
        (habit) => Number(habit.completed) === 1
    ).length;
    
    
        
    return (
        <div className="calendar-page">
            <div className="calendar-card">
                <div className="view-switch">
                    <button
                    className="view-tab"
                    onClick={() => navigate("/tracker")}
                    >
                      Daily view
                    </button>

                    <button className="view-tab active">
                        Calendar view
                    </button>
                </div>

                <div className="calendar-header">
                    <h2>{formatMonthYear(currentMonth)}</h2>

                    <div className="calendar-nav">
                        <button
                        className="calendar-arrow"
                        onClick={() => changeMonth(-1)}
                    >
                      ‹
                      </button>

                      <button className="calendar-arrow"
                      onClick={() => changeMonth(1)}
                      >
                      ›
                      </button>
                    </div>
                </div>

                <div className="calendar-weekdays">
                    <span>Sun</span>
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
            </div>

            <div className="calendar-grid">
                {getDaysInMonth(currentMonth).map((day, index) =>
                day ? (
                    <button
                    key={index}
                    className={`calendar-day ${
                        isSameDay(day, selectedDate) ? "selected" : ""
                    } ${
                        completedDates.has(formatDate(day)) ? "has-completion" : ""
                    } ${
                        isFutureDay(day) ? "disabled-day" : ""
                    }`}
                    onClick={() => {
                        if (!isFutureDay(day)) {
                        setSelectedDate(day);
                    }
                }}
                
                disabled={isFutureDay(day)}
                   
                >
                    {day.getDate()}
                        </button>
                ) : (
                    <div key={index} className="calendar-empty"></div>
                )
                )}
                </div>

                <div className="calendar-progress-box">
                    <h3>
                        Progress for {selectedDate.toLocaleDateString("en-GB")}
                    </h3>
                    
                    <p>
                        {completedCount} of {dayHabits.length} habits completed
                    </p>

                    {dayHabits.length === 0 ? (
                    <div className="progress-item">
                        <span>No habits for this day yet.</span>
                    </div>
                    ) : (
                        dayHabits.map((habit) => (
                            <div className="progress-item" key={habit.id}>
                                <span>{habit.title}</span>

                        <div className="progress-right">
                        <span>
                            {Number(habit.completed) === 1 ? "Done" : "Not done"}
                        </span>

                        <div className="mini-progress">
                            <div
                            className="mini-progress-fill"
                            style={{
                                width: Number(habit.completed) === 1 ? "100%" : "0%"
                            }}
                        ></div>
                   </div>
                </div>
                </div>
           ))
            )}
        </div>
        </div>
        </div>
    );
}
                    