//mycode
import {useEffect, useState } from "react";
import "./reports.css";

export default function Reports() {
    const [report, setReport] = useState(null);
    const [error, setError] = useState("");


    //load the report data when the page opens
    useEffect(() => {
        async function loadReport() {
            try {
                //request report dara from the backend API
                const res = await fetch("/habitlilli/api/report.php", {
                   credentials: "include",
                });
                
                //request reprt data from the backend api 
                const data = await res.json();

                //shown an error if the backend reponse is not successful
                if (!res.ok) {
                    throw new Error(data.error || "Failed to load report");
                }
                //save the report data into state
                setReport(data);
            } catch (error) {
                //save the error message so that it can be shown on the screen
                setError(error.message || "Something went wrong");
             }
            }

            loadReport();  
        }, []);

        //shown an error card if the report could not be loaded
        if (error) {
            return (
                <div className="reports-page">
                    <h1 className="reports-title">Habit Progress Report</h1>
                    <div className="report-card error-card">
                        <p>Error: {error}</p>
                    </div>
                </div>
            );
           }
           
           //show a loading message while waiting for the backend response
           if (!report) {
            return (
                <div className="reports-page">
                    <h1 className="reports-title">Habit Progress Report</h1>
                    <div className="report-card loading-card">
                        <p>Loading report...</p>
                    </div>
                </div>
             );
           }
           
           //check whether the chart has any real progress data
           const hasChartData = report.chart.some((item) => item.count > 0);
        
           //find the day with the highest number of completions
           const bestDay = hasChartData
           ? [...report.chart].sort((a, b) => b.count - a.count)[0]
           : null;
           
           //find the day with the lowest number of completions
           const worstDay = hasChartData
           ? [...report.chart].sort((a, b) => a.count - b.count)[0]
           : null;

           return (
            <div className="reports-page">
                <h1 className="reports-title">Habit Progress Report</h1>

                <p className="reports-subtitle">
                    Track your weekly and monthly consistency
                </p>
                
                <button
                        className="back-btn"
                        onClick={() => window.location.href = "/habitlilli/dashboard.php"}
                     >
                        Back to Dashboard
                    </button>

                <div className="reports-grid">
                    <div className="report-card weekly-card">
                        <div className="card-header">
                            <h2>Weekly Summary</h2>
                        </div>

                        <div className="report-lines">
                            <p>
                                <strong>Completed This Week:</strong> {report.weekly.completed}
                            </p>

                            <p>
                                <strong>Success Rate:</strong> {report.weekly.successRate}%
                            </p>

                            <p className="habit-line">
                                <span className="dot green"></span>
                                <strong>Best Habit:</strong> {report.weekly.bestHabit}
                           </p>

                            <p className="habit-line">
                                <span className="dot red"></span>
                                <strong>Needs Improvement:</strong>{" "}
                                {report.weekly.weakestHabit}
                            </p>
                        </div>
                    </div>

                    <div className="report-card monthly-card">
                        <div className="card-header">
                            <h2>Monthly Summary</h2>
                        </div>

                        <div className="report-lines">
                            <p>
                                <strong>Total Completions:</strong> {report.monthly.completed}
                            </p>
                            <p>
                                <strong>Avg Success Rate:</strong> {report.monthly.successRate}%
                            </p>
                            <p>
                                <strong>Active Days:</strong> {report.monthly.activeDays}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="report-card chart-card">
                    <div className="card-header">
                        <h2>Progress Overview</h2>
                    </div>

                    <div className="chart-area">
                        {report.chart.map((item, index) => (
                            <div className="chart-bar-wrap" key={index}>
                                <div className="chart-track">
                                <div 
                                className="chart-bar"
                                style={{ height: `${Math.max(item.count * 70, 20)}px` }}
                                title={`${item.day}: ${item.count} completions`}
                        ></div>
                    </div>
                    <span className="chart-label">{item.day}</span>

                    <span className="chart-count">
                        {item.count} done
                    </span>
                </div>
             ))}
            </div>

            <div className="chart-footer">
                {hasChartData ? (
                   <>
                You are most consistent on <strong>{bestDay.day}</strong>. Try to 
                improve consistency on <strong>{worstDay.day}</strong>.
            </>
                ) : (
                    <>No progress data yet. Start completing habits to see your consistency insights.</>
                )}
            </div>
            </div>
            </div>
        );
    }
           